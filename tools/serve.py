#!/usr/bin/env python
"""
Servidor estático de desenvolvimento do site (raiz do projeto).

  python tools/serve.py [porta]        # padrão 5500

Igual ao "python -m http.server --bind ::", mas:
  - responde em IPv4 e IPv6 (localhost, 127.0.0.1 e [::1]);
  - manda Cache-Control: no-cache — o navegador sempre revalida HTML/CSS/JS/dados, então uma
    página recém-gerada nunca é misturada com arquivos antigos do cache.
"""
import os
import socket
import sys
from functools import partial
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


class NoCacheHandler(SimpleHTTPRequestHandler):
    def end_headers(self):
        self.send_header('Cache-Control', 'no-cache, must-revalidate')
        super().end_headers()

    def log_message(self, fmt, *args):  # log curto
        sys.stderr.write('%s %s\n' % (self.command if hasattr(self, 'command') else '', fmt % args))


class DualStackServer(ThreadingHTTPServer):
    address_family = socket.AF_INET6

    def server_bind(self):
        try:
            self.socket.setsockopt(socket.IPPROTO_IPV6, socket.IPV6_V6ONLY, 0)
        except (AttributeError, OSError):
            pass
        super().server_bind()


if __name__ == '__main__':
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 5500
    handler = partial(NoCacheHandler, directory=ROOT)
    with DualStackServer(('::', port), handler) as httpd:
        print('Servindo %s em http://localhost:%d (sem cache)' % (ROOT, port), flush=True)
        httpd.serve_forever()
