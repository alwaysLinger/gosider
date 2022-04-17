package hub

import (
	"bufio"
	"context"
	"encoding/binary"
	"github.com/alwaysLinger/gosider/pkg/concrete"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/alwaysLinger/gosider/pkg/sinterface"
)

type hubOptions struct {
	stream  *bufio.Reader
	ctx     context.Context
	th      func(context.Context, interface{}) (interface{}, error)
	resChan chan sinterface.IResponse
	proto   bool
}

var defaultHubOptions = hubOptions{
	stream:  bufio.NewReader(os.Stdin),
	ctx:     context.Background(),
	resChan: make(chan sinterface.IResponse, 1),
	proto:   true,
}

type HubOption interface {
	apply(*hubOptions)
}

type funcHubOption struct {
	f func(options *hubOptions)
}

func (fdo *funcHubOption) apply(do *hubOptions) {
	fdo.f(do)
}

func newFuncHubOption(f func(options *hubOptions)) *funcHubOption {
	return &funcHubOption{
		f: f,
	}
}

type Hub struct {
	stream  *bufio.Reader
	ctx     context.Context
	resChan chan sinterface.IResponse
	th      func(context.Context, interface{}) (interface{}, error)
	proto   bool
}

func (h *Hub) recv() {
	for {
		t := concrete.NewTask(h.th, h.proto)
		head, err := h.stream.Peek(8)
		if err == bufio.ErrNegativeCount {
			continue
		}
		t.MsgLen = binary.BigEndian.Uint32(head[4:])
		payload, err := h.stream.Peek(8 + int(t.MsgLen))
		if err == bufio.ErrNegativeCount {
			continue
		}
		t.Parse(payload)
		h.stream.Read(t.Payload)

		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", t.Payload, 0644)

		t.TaskId = binary.BigEndian.Uint32(payload[0:4])
		copy(t.Msg, payload[8:])
		go h.handleTask(t)
	}
}

func (h *Hub) Start() {
	if h.th == nil {
		panic("set recv handler first")
	}
	go h.recv()
	go h.response()

	sig := make(chan os.Signal)
	signal.Notify(sig, syscall.SIGTERM)
	<-sig
}

func (h *Hub) response() {
	for {
		res := <-h.resChan
		res.Response()
	}
}

func (h *Hub) handleTask(t sinterface.ITask) {
	ctx, cancel := context.WithTimeout(h.ctx, time.Second)
	defer cancel()
	if r, err := t.Handle(ctx); err != nil {
		// 	TODO
	} else {
		h.resChan <- r
	}
}

func NewHub(th func(context.Context, interface{}) (interface{}, error), opt ...HubOption) *Hub {
	opts := defaultHubOptions
	for _, o := range opt {
		o.apply(&opts)
	}
	return &Hub{
		stream:  opts.stream,
		ctx:     opts.ctx,
		th:      th,
		resChan: opts.resChan,
		proto:   opts.proto,
	}
}

func CustomHubStream(s *bufio.Reader) HubOption {
	return newFuncHubOption(func(options *hubOptions) {
		options.stream = s
	})
}

func CustomHubCtx(ctx context.Context) HubOption {
	return newFuncHubOption(func(options *hubOptions) {
		options.ctx = ctx
	})
}

func CustomHubTh(th func(context.Context, interface{}) (interface{}, error)) HubOption {
	return newFuncHubOption(func(options *hubOptions) {
		options.th = th
	})
}

func CustomHubReschan(ch chan sinterface.IResponse) HubOption {
	return newFuncHubOption(func(options *hubOptions) {
		options.resChan = ch
	})
}

func CustomHubProto(t bool) HubOption {
	return newFuncHubOption(func(options *hubOptions) {
		options.proto = t
	})
}
