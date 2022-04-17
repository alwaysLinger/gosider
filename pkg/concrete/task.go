package concrete

import (
	"context"
	"encoding/binary"
	"github.com/alwaysLinger/gosider/pkg/pb"
	"github.com/alwaysLinger/gosider/pkg/sinterface"
	"google.golang.org/protobuf/proto"
	"io/ioutil"
)

type task struct {
	TaskId  uint32
	MsgLen  uint32
	Msg     []byte
	Payload []byte
	onTask  func(context.Context, interface{}) (interface{}, error)
	res     sinterface.IResponse
	err     error
	proto   bool
	task    *pb.Task
	reply   pb.Reply
}

func (t *task) Parse(p []byte) {
	t.Payload = make([]byte, len(p))
	t.Msg = make([]byte, len(p)-8)
}

func (t *task) Handle(ctx context.Context) (sinterface.IResponse, error) {
	select {
	case <-ctx.Done():
		return nil, ctx.Err()
	case <-t.wrapHandle(ctx):
		return t.res, t.err
	}
}

func (t *task) wrapHandle(ctx context.Context) <-chan struct{} {
	c := make(chan struct{})
	go func() {
		var p interface{}
		p = t.Msg
		if t.proto {
			var req pb.Task
			proto.Unmarshal(t.Msg, &req)
			p = &req
		}
		ret, err := t.onTask(ctx, p)
		if err != nil {
			t.err = err
		} else {
			defer func() {
				c <- struct{}{}
			}()
			var rep []byte
			if _, ok := ret.([]byte); !ok {
				rep, _ = proto.Marshal(ret.(*pb.Reply))
				ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", rep, 0644)
			} else {
				rep = ret.([]byte)
			}
			resp := make([]byte, 8+len(rep))
			binary.BigEndian.PutUint32(resp[0:4], t.TaskId)
			binary.BigEndian.PutUint32(resp[4:8], uint32(len(rep)))
			resp = append(resp[0:8], rep...)
			t.res = NewResponse(resp)
		}
	}()
	return c
}

func NewTask(h func(context.Context, interface{}) (interface{}, error), t bool) *task {
	return &task{
		onTask: h,
		proto:  t,
	}
}
