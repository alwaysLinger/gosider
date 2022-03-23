package sinterface

import "context"

type ITask interface {
	Handle(ctx context.Context) (IResponse, error)
}
