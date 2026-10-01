#!/usr/bin/env python3
"""Static preview server — phục vụ ui_preview.html ở root."""
import http.server
import socketserver
import functools
import os
import urllib.parse

ROOT = '/home/user/Ban_linh_kien'


class Handler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        parsed = urllib.parse.urlparse(self.path)
        if parsed.path in ('/', '/index.html', '/index.php'):
            self.path = '/ui_preview.html'
        return super().do_GET()


def main():
    os.chdir(ROOT)
    handler = functools.partial(Handler, directory=ROOT)
    socketserver.TCPServer.allow_reuse_address = True
    with socketserver.TCPServer(("0.0.0.0", 8080), handler) as httpd:
        print("Preview server running on :8080")
        httpd.serve_forever()


if __name__ == '__main__':
    main()
