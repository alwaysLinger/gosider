package concrete

import (
	"context"
	"encoding/binary"
	"github.com/alwaysLinger/gosider/pkg/sinterface"
)

type task struct {
	TaskId  uint32
	MsgLen  uint32
	Msg     []byte
	Payload []byte
	onTask  func(context.Context, []byte) ([]byte, error)
	res     sinterface.IResponse
	err     error
}

func (t *task) Parse(p []byte) {
	t.Payload = make([]byte, len(p))
}

func (t task) Handle(ctx context.Context) (sinterface.IResponse, error) {
	select {
	case <-ctx.Done():
		return nil, ctx.Err()
	case <-t.wrapHandle(ctx):
		return t.res, t.err
	}
}

func (t *task) wrapHandle(ctx context.Context) <-chan struct{} {
	c := make(chan struct{})
	ret, err := t.onTask(ctx, t.Payload)
	if err != nil {
		t.err = err
	} else {
		resp := make([]byte, 8+len(ret))
		binary.BigEndian.PutUint32(resp[0:4], t.TaskId)
		binary.BigEndian.PutUint32(resp[4:8], uint32(len(ret)))
		resp = append(resp[0:8], ret...)
		t.res = NewResponse(resp)
	}
	return c
}

func NewTask(h func(context.Context, []byte) ([]byte, error)) *task {
	return &task{
		onTask: h,
	}
}
