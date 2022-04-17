package main

import (
	"context"
	"encoding/json"
	"github.com/alwaysLinger/gosider/pkg/hub"
	"github.com/alwaysLinger/gosider/pkg/pb"
)

func main() {
	h := hub.NewHub(func(ctx context.Context, req interface{}) (interface{}, error) {

		// task, ok := req.(*pb.Task)
		// if ok {
		// 	ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", task.GetTask(), 0644)
		// } else {
		// 	ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", []byte("fail"), 0644)
		// }
		// return nil, nil

		// defer func() {
		// 	err := recover()
		// 	s := fmt.Sprintf("%+v", err)
		// 	ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", []byte(s), 0644)
		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", []byte(s), 0644)
		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", task.GetTask(), 0644)
		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", task.GetTask(), 0644)
		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", task.GetTask(), 0644)
		// }()
		// task := req.(*pb.Task)

		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", task.GetTask(), 0644)
		// fmt.Fprintln(os.Stdout, task.TaskId)
		//
		// var bb map[string]interface{}
		// err := json.Unmarshal(task.GetContext(), &bb)
		// if err != nil {
		// 	fmt.Fprintln(os.Stdout, err)
		// 	return nil, err
		// }
		// fmt.Fprintf(os.Stdout, "%+v\n", &bb)

		// return &pb.Reply{
		// 	TaskId:   0,
		// 	Status:   0,
		// 	Response: task.Context,
		// 	Context:  task.GetTask(),
		// }, nil

		task, _ := req.(*pb.Task)
		// ss := task.String()
		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", []byte(ss), 0644)

		// var a map[string]interface{}
		// json.Unmarshal(task.GetContext(), &a)
		// s := fmt.Sprintf("%+v", a)
		// ioutil.WriteFile("/Users/al/code/go/yolo/gosider/debug.txt", []byte(s), 0644)

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
