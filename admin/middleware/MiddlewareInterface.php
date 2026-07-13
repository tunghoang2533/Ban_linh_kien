<?php
/**
 * MiddlewareInterface — Interface cho tất cả middleware trong admin pipeline
 * 
 * Mỗi middleware nhận request context và có thể:
 * - Cho phép request đi tiếp (return true)
 * - Chặn request và redirect (return false, set response)
 */
interface MiddlewareInterface
{
    /**
     * @param array &$context  Request context (db, admin, page, action, ...)
     * @return bool            true = tiếp tục pipeline, false = dừng
     */
    public function handle(array &$context): bool;
}
