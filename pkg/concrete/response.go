package concrete

import (
	"bytes"
	"io"
	"os"
)

var res Response

type Response struct {
	buf *bytes.Buffer
	w   io.Writer
}

func (r *Response) Buf() *bytes.Buffer {
	return r.buf
}

func (r *Response) Response() {
	r.Write(r.buf.Bytes())
}

func (r *Response) Write(p []byte) (n int, err error) {
	return r.w.Write(p)
}

func (r *Response) SetWriter(w io.Writer) {
	r.w = w
}

func NewResponse(r []byte) *Response {
	return &Response{
		buf: bytes.NewBuffer(r),
		w:   os.Stdout,
	}
}
