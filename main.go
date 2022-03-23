package main

import (
	"context"
	"github.com/alwaysLinger/gosider/pkg/hub"
)

func main() {
	h := hub.NewHub(func(ctx context.Context, bytes []byte) ([]byte, error) {
		return []byte{'a', 'b', 'c'}, nil
	})
	h.Start()
}
