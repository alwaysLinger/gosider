package sinterface

import "io"

type IResponse interface {
	io.Writer
	SetWriter(io.Writer)
	Response()
}
