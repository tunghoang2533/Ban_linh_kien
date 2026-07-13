<?php
/**
 * MiddlewareRunner — Pipeline chạy tuần tự các middleware
 */
class MiddlewareRunner
{
    /** @var MiddlewareInterface[] */
    private array $middlewares = [];

    /**
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $mw) {
            $this->add($mw);
        }
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Chạy pipeline qua tất cả middleware.
     * Nếu middleware nào return false → dừng ngay.
     *
     * @param array &$context
     * @return bool  true = tất cả pass, false = bị chặn
     */
    public function run(array &$context): bool
    {
        foreach ($this->middlewares as $mw) {
            if (!$mw->handle($context)) {
                return false;
            }
        }
        return true;
    }
}
