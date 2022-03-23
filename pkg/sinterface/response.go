package sinterface

import "io"

type IResponse interface {
	io.Writer
	Response()
}
