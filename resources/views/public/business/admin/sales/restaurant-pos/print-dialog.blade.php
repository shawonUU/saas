<style>
#rpos-print-preview,#rpos-shortcut-help{position:fixed;inset:0;margin:auto;width:min(680px,calc(100% - 24px));max-height:92dvh;padding:0;border:1px solid var(--rp-border);border-radius:16px;background:var(--rp-panel);color:var(--rp-ink);box-shadow:0 24px 80px #0005;overflow:hidden}
#rpos-print-preview[open],#rpos-shortcut-help[open]{display:flex;flex-direction:column}
#rpos-print-preview::backdrop,#rpos-shortcut-help::backdrop{background:#10152599;backdrop-filter:blur(3px)}
.rpos-print-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 18px;border-bottom:1px solid var(--rp-border);flex:none}
.rpos-print-head h2{font-size:17px;font-weight:750;margin:0}
.rpos-print-head button{width:38px;height:38px;border:1px solid var(--rp-border);border-radius:9px;background:var(--rp-soft);color:var(--rp-ink);font-size:23px;line-height:1;cursor:pointer}
.rpos-print-stage{height:min(62dvh,620px);min-height:230px;position:relative;background:#eef0f4;overflow:hidden}
#rpos-print-frame{width:100%;height:100%;border:0;display:block;background:#fff;color-scheme:light}
#rpos-print-status{display:grid;place-content:center;gap:12px;text-align:center;height:100%;padding:28px;color:#4a5260;font-size:14px}
#rpos-print-status button{justify-self:center}
.rpos-print-footer{display:flex;justify-content:flex-end;gap:10px;padding:12px 18px;border-top:1px solid var(--rp-border);flex:none}
.rpos-print-footer .ret-btn{min-width:140px}
#rpos-shortcut-help{width:min(480px,calc(100% - 24px))}
.rpos-shortcut-list{display:grid;grid-template-columns:auto minmax(0,1fr);gap:12px 20px;align-items:center;padding:20px;margin:0;overflow:auto;font-size:14px}
.rpos-shortcut-list dt,.rpos-shortcut-list dd{margin:0}
.rpos-shortcut-list kbd{display:inline-block;min-width:40px;text-align:center;padding:5px 9px;background:var(--rp-soft);border:1px solid var(--rp-border);border-radius:7px;font:700 12px ui-monospace,monospace;color:var(--rp-ink)}
.rpos-shortcut-note{margin:0;padding:0 20px 18px;color:var(--rp-muted);font-size:12px;line-height:1.5}
@media(max-width:520px){#rpos-print-preview{width:calc(100% - 16px);max-height:94dvh}.rpos-print-stage{height:66dvh}.rpos-print-head,.rpos-print-footer{padding:12px}.rpos-print-footer .ret-btn{width:100%}.rpos-shortcut-list{gap:11px 15px;padding:16px;font-size:13px}}
</style>
<dialog id="rpos-print-preview" aria-labelledby="rpos-print-title">
    <div class="rpos-print-head"><h2 id="rpos-print-title">{{ t('Print preview','প্রিন্ট প্রিভিউ') }}</h2><button type="button" id="rpos-print-close" aria-label="{{ t('Close print preview','প্রিন্ট প্রিভিউ বন্ধ করুন') }}">×</button></div>
    <div class="rpos-print-stage" aria-busy="false" id="rpos-print-stage">
        <div id="rpos-print-status" role="status" aria-live="polite"><span id="rpos-print-message">{{ t('Loading preview…','প্রিভিউ লোড হচ্ছে…') }}</span><button type="button" id="rpos-print-retry" class="ret-btn alt" hidden>{{ t('Retry','আবার চেষ্টা করুন') }}</button></div>
        <iframe id="rpos-print-frame" title="{{ t('Bill or kitchen ticket preview','বিল বা কিচেন টিকিটের প্রিভিউ') }}" sandbox="allow-same-origin allow-modals" referrerpolicy="same-origin" hidden></iframe>
    </div>
    <div class="rpos-print-footer"><button type="button" id="rpos-print-confirm" class="ret-btn" disabled>{{ t('Print','প্রিন্ট') }} <span aria-hidden="true">· Ctrl P</span></button></div>
</dialog>
<dialog id="rpos-shortcut-help" aria-labelledby="rpos-shortcut-title">
    <div class="rpos-print-head"><h2 id="rpos-shortcut-title">{{ t('Keyboard shortcuts','কিবোর্ড শর্টকাট') }}</h2><button type="button" id="rpos-shortcut-close" aria-label="{{ t('Close keyboard shortcuts','কিবোর্ড শর্টকাট বন্ধ করুন') }}">×</button></div>
    <dl class="rpos-shortcut-list">
        <dt><kbd>F2</kbd></dt><dd>{{ t('Search food or tables','খাবার বা টেবিল খুঁজুন') }}</dd>
        <dt><kbd>F3</kbd></dt><dd>{{ t('Tables / current order · dine-in','টেবিল / বর্তমান অর্ডার · ডাইন-ইন') }}</dd>
        <dt><kbd>F4</kbd></dt><dd>{{ t('New order','নতুন অর্ডার') }}</dd>
        <dt><kbd>F6</kbd></dt><dd>{{ t('Hold order','অর্ডার ধরে রাখুন') }}</dd>
        <dt><kbd>F8</kbd></dt><dd>{{ t('Receive / confirm payment','পেমেন্ট গ্রহণ / নিশ্চিত করুন') }}</dd>
        <dt><kbd>F9</kbd></dt><dd>{{ t('Bill preview','বিলের প্রিভিউ') }}</dd>
        <dt><kbd>F10</kbd></dt><dd>{{ t('Kitchen ticket preview','কিচেন টিকিটের প্রিভিউ') }}</dd>
        <dt><kbd>Ctrl / ⌘ P</kbd></dt><dd>{{ t('Open bill / print current preview','বিল খুলুন / খোলা প্রিভিউ প্রিন্ট করুন') }}</dd>
        <dt><kbd>Esc</kbd></dt><dd>{{ t('Close preview or shortcuts','প্রিভিউ বা শর্টকাট বন্ধ করুন') }}</dd>
        <dt><kbd>?</kbd></dt><dd>{{ t('Show shortcuts when not typing','লেখার বাইরে শর্টকাট দেখুন') }}</dd>
    </dl>
    <p class="rpos-shortcut-note">{{ t('Some keyboards require Fn with the function keys. Shortcuts follow the same permissions and order checks as the buttons.','কিছু কিবোর্ডে ফাংশন কির সঙ্গে Fn চাপতে হয়। শর্টকাটেও বাটনের একই অনুমতি ও অর্ডারের নিয়ম প্রযোজ্য।') }}</p>
</dialog>
