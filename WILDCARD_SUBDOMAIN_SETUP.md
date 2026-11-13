# Automatic wildcard business subdomains (Laravel + Windows/Laragon)

## What is automatic?
- Creating `rohimstore` in `saas.test:8000` inserts a Business row and the owner account in one transaction.
- Its storefront is `http://rohimstore.saas.test:8000`.
- Its admin login is `http://admin.rohimstore.saas.test:8000`.
- `http://admin.saas.test:8000` is reserved for SaaS Authority.
- Additional names are handled by the existing Laravel `Route::domain('{business}...')` routing and business middleware. Unknown or unapproved business codes give 404.
- Laravel does NOT create public DNS records; a DNS wildcard must be configured once.

## Windows / Laragon: one-time DNS wildcard
Windows `hosts` does not support wildcard names. Use Acrylic DNS Proxy (free):
https://mayakron.altervista.org/support/acrylic/Home.htm

1. Install Acrylic DNS Proxy as Administrator.
2. Open **Acrylic Hosts** from the Acrylic UI; add this one line to `AcrylicHosts.txt`:

   ```text
   127.0.0.1 >saas.test
   ```

   It maps `saas.test` and every nested subdomain such as `admin.rohimstore.saas.test` to `127.0.0.1`.
   Alternatively, from an Administrator PowerShell prompt at project root (after installing Acrylic):

   ```powershell
   .\scripts\setup-wildcard-dns.ps1
   ```

   The script backs up the AcrylicHosts file, adds the wildcard rule only once, restarts the Acrylic service and flushes Windows DNS cache. It deliberately does NOT alter your network adapter.
3. Set the active Windows network adapter's Preferred IPv4 DNS server to `127.0.0.1` (and optionally IPv6 `::1` when using Acrylic IPv6). Set up the service first to avoid losing name resolution. See Acrylic's official Windows configuration instructions.
4. Restart Acrylic service if configured manually, then run `ipconfig /flushdns`.
5. In your Laravel `.env` use:

   ```env
   APP_URL=http://saas.test:8000
   SAAS_DOMAIN=saas.test
   SESSION_DOMAIN=null
   SESSION_SECURE_COOKIE=false
   ```

6. Start from PowerShell:

   ```powershell
   php artisan optimize:clear
   php artisan serve --host=127.0.0.1 --port=8000
   ```

7. Verify with PowerShell:

   ```powershell
   Resolve-DnsName saas.test -Server 127.0.0.1
   Resolve-DnsName admin.saas.test -Server 127.0.0.1
   Resolve-DnsName rohimstore.saas.test -Server 127.0.0.1
   Resolve-DnsName admin.rohimstore.saas.test -Server 127.0.0.1
   ```

   Each result should contain `127.0.0.1`. Then open these names in the browser with `http://` and `:8000`. If a browser uses its own secure DNS (DoH), disable that setting for local development or point it at the Windows system resolver.

**Note:** Mapping the real `saas.test` to localhost makes the real Internet website inaccessible from this computer until the rule is removed. For local-only projects you may instead use `saas.test` consistently in `.env` and Acrylic.

## Production: one-time DNS / server wildcard
- Point apex `saas.test` to the public server IP via an A record.
- Set a wildcard DNS record `*.saas.test` to that same IP (or suitable reverse proxy). Ensure nested names such as `admin.rohimstore.saas.test` resolve correctly; avoid adding conflicting more-specific DNS records without checking wildcard coverage.
- Configure Nginx/Apache/reverse proxy to accept `saas.test`, `*.saas.test`, and nested `admin.<business>.saas.test`, all using the same Laravel `public` directory.
- Use `APP_URL=https://saas.test`, `SAAS_DOMAIN=saas.test`, and HTTPS/secure session cookies in production.
- **TLS caveat:** a certificate for `*.saas.test` covers `rohimstore.saas.test` but NOT `admin.rohimstore.saas.test`. Set up per-business admin hostname certificate issuance or another supported automated certificate solution before production launch. Plain `php artisan serve` on port 8000 is only for local development.

No new migration is needed. The existing `businesss.code` unique index and reserved-subdomain validation still apply.
