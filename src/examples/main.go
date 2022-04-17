package main

import (
	"context"
	"encoding/json"

	"github.com/alwaysLinger/gosider/pkg/hub"
	"github.com/alwaysLinger/gosider/pkg/pb"
)

func main() {
	h := hub.NewHub(func(ctx context.Context, req interface{}) (interface{}, error) {
		task, _ := req.(*pb.Task)

		return &pb.Reply{
			TaskId:   task.GetTaskId(),
			Status:   123,
			Response: jsonResp(),
			Context:  task.GetContext(),
		}, nil
	})

	h.Start()
}

type resp struct {
	FieldA string `json:"field_a"`
	FieldB string `json:"field_b"`
	FieldC string `json:"field_c"`
}

func jsonResp() []byte {

	marshal, err := json.Marshal(resp{
		FieldA: "hello",
		FieldB: "swoole",
		FieldC: "golang",
	})
	if err != nil {
		return nil
	}
	return marshal
}
