package hub

import (
	"bufio"
	"context"
	"encoding/binary"
	"github.com/alwaysLinger/gosider/pkg/concrete"
	"github.com/alwaysLinger/gosider/pkg/sinterface"
	"os"
	"os/signal"
	"syscall"
	"time"
)

type Hub struct {
	// buf    *bytes.Buffer
	stream  *bufio.Reader
	ctx     context.Context
	resChan chan sinterface.IResponse
	th      func(context.Context, []byte) ([]byte, error)
}

func (h *Hub) recv() {
	for {
		t := concrete.NewTask(h.th)
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
		t.TaskId = binary.BigEndian.Uint32(payload[0:4])
		t.Msg = payload[8:]
		go h.handleTask(t)
	}
}

func (h *Hub) Start() {
	if h.th == nil {
		os.Exit(-1)
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

func NewHub(th func(context.Context, []byte) ([]byte, error)) *Hub {
	// buf := bytes.NewBuffer([]byte{})
	return &Hub{
		// buf:    buf,
		stream:  bufio.NewReader(os.Stdin),
		ctx:     context.Background(),
		th:      th,
		resChan: make(chan sinterface.IResponse, 1),
	}
}
