(() => {
  const TOAST_ROOT_ID = "toast-root";

  function ensureRoot() {
    let root = document.getElementById(TOAST_ROOT_ID);
    if (root) return root;

    root = document.createElement("div");
    root.id = TOAST_ROOT_ID;
    // Bottom-right, stack vertically (newest on bottom).
    root.className =
      "fixed bottom-6 right-4 sm:right-6 z-[9999] w-[min(420px,calc(100vw-2rem))] flex flex-col gap-3";
    document.body.appendChild(root);
    return root;
  }

  function stylesFor(type) {
    // Background is always gray-800; color accent is in the icon.
    return "bg-gray-800 text-white";
  }

  // Heroicons (outline). Icon color depends on type.
  function iconSvg(type) {
    const t = type || "info";
    if (t === "success") {
      return (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' +
        'stroke="currentColor" stroke-width="1.5" class="w-5 h-5 text-emerald-400">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />' +
        "</svg>"
      );
    }
    if (t === "error") {
      return (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' +
        'stroke="currentColor" stroke-width="1.5" class="w-5 h-5 text-rose-400">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008Z" />' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />' +
        "</svg>"
      );
    }
    if (t === "warning") {
      return (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' +
        'stroke="currentColor" stroke-width="1.5" class="w-5 h-5 text-amber-300">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008Z" />' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a1.5 1.5 0 0 0 1.29 2.25h17.78A1.5 1.5 0 0 0 22.18 18L13.71 3.86a1.5 1.5 0 0 0-2.42 0Z" />' +
        "</svg>"
      );
    }
    return (
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' +
      'stroke="currentColor" stroke-width="1.5" class="w-5 h-5 text-sky-400">' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h1.5v5.25h-1.5z" />' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25h.008v.008H12V8.25Z" />' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />' +
      "</svg>"
    );
  }

  // Heroicons: XMark (outline)
  function closeIconSvg() {
    return (
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' +
      'stroke="currentColor" stroke-width="1.5" class="w-5 h-5">' +
      '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />' +
      "</svg>"
    );
  }

  function showToast(message, type = "info", opts = {}) {
    const root = ensureRoot();
    const duration = Number.isFinite(opts.duration) ? opts.duration : 3000;

    const toast = document.createElement("div");
    toast.setAttribute("role", "status");
    toast.setAttribute("aria-live", "polite");
    toast.className =
      "rounded-none shadow-lg border border-black px-5 py-4 flex items-center gap-4 " +
      "transition-opacity duration-200 " +
      stylesFor(type);

    const icon = document.createElement("div");
    icon.className = "flex-shrink-0";
    icon.innerHTML = iconSvg(type);

    const text = document.createElement("div");
    // Match client/admin typography: slightly smaller, medium weight.
    text.className = "text-sm leading-5 font-medium flex-1";
    text.textContent = String(message || "");

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "opacity-90 hover:opacity-100 flex-shrink-0";
    btn.setAttribute("aria-label", "Закрыть");
    btn.innerHTML = closeIconSvg();

    let removed = false;
    function remove() {
      if (removed) return;
      removed = true;
      toast.style.opacity = "0";
      setTimeout(() => toast.remove(), 200);
    }

    btn.addEventListener("click", remove);
    toast.appendChild(icon);
    toast.appendChild(text);
    toast.appendChild(btn);
    root.appendChild(toast);

    if (duration > 0) {
      setTimeout(remove, duration);
    }
  }

  window.showToast = showToast;
})();
