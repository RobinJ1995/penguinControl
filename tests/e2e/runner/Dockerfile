# syntax=docker/dockerfile:1
FROM python:3.13-slim

ENV PYTHONUNBUFFERED=1 \
    PLAYWRIGHT_BROWSERS_PATH=/opt/pw-browsers \
    NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt

# See tests/e2e/app/Dockerfile: optional CA for building behind a TLS-intercepting
# proxy. Does nothing when the secret is not supplied.
RUN --mount=type=secret,id=extra_ca,target=/tmp/extra-ca.crt \
    if [ -s /tmp/extra-ca.crt ]; then \
      cp /tmp/extra-ca.crt /usr/local/share/ca-certificates/extra-ca.crt ; \
      chmod 0644 /usr/local/share/ca-certificates/extra-ca.crt ; \
      update-ca-certificates ; \
      echo 'Acquire::https::CaInfo "/usr/local/share/ca-certificates/extra-ca.crt";' \
        > /etc/apt/apt.conf.d/99-extra-ca ; \
      sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources ; \
    fi

WORKDIR /work

COPY runner/requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt \
 && playwright install --with-deps chromium

COPY runner/behave.ini ./behave.ini

CMD ["behave", "--no-capture", "--format", "pretty"]
