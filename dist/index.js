import { router as t } from "@inertiajs/vue3";
const a = (e) => {
  t.reload({
    only: ["page", "blocks"],
    headers: { "X-Skip-Shared": "1" },
    async: !0
  });
}, i = (e) => {
  if (typeof window > "u")
    return;
  const o = document.getElementById(e.block);
  o && (o.style.setProperty("scroll-margin-top", "var(--nav-height)"), o.scrollIntoView({ behavior: "smooth" }));
};
function l(e) {
  return {
    "fabriq-preview-ready": () => a(),
    "fabriq-locate-block": () => i(e)
  }[e.type] ?? (() => console.warn("no event handler for ", e.type));
}
const c = {
  install: (e, o) => {
    if (typeof window > "u")
      return;
    const n = (r) => {
      if (r.origin !== o.fabriq_url) {
        console.warn("host not allowed", r.origin);
        return;
      }
      if (r.type !== "message") {
        console.warn("event type not allowed", r.type);
        return;
      }
      l(r.data)();
    };
    window.addEventListener("message", n);
  }
};
export {
  c as FabriqPreview
};
