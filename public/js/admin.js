(() => {
  const qs = (sel) => document.querySelector(sel);
  const qsa = (sel) => Array.from(document.querySelectorAll(sel));

  const postJson = async (url, body) => {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: body ? JSON.stringify(body) : undefined,
    });
    return res;
  };

  const logout = async () => {
    try {
      await fetch("/api/auth/logout", { method: "POST" });
    } catch {
      // ignore
    } finally {
      window.location.href = "/login";
    }
  };

  const initSidebar = () => {
    const sidebar = qs("#adminSidebar");
    const overlay = qs("#adminOverlay");
    const toggle = qs("#adminSidebarToggle");

    if (!sidebar || !overlay) return;

    const openClasses = ["translate-x-0", "lg:w-56"];
    const closedClasses = ["-translate-x-full", "lg:w-0"];

    const isDesktop = () => window.matchMedia("(min-width: 1024px)").matches;

    const setOpen = (open) => {
      if (open) {
        sidebar.classList.remove(...closedClasses);
        sidebar.classList.add(...openClasses);
        if (!isDesktop()) overlay.classList.remove("hidden");
      } else {
        sidebar.classList.remove(...openClasses);
        sidebar.classList.add(...closedClasses);
        overlay.classList.add("hidden");
      }
    };

    // Initial state: open on desktop, closed on mobile.
    try {
      setOpen(isDesktop());
    } catch {
      setOpen(false);
    }

    if (toggle) {
      toggle.addEventListener("click", () => {
        const isOpenNow = sidebar.classList.contains("translate-x-0");
        setOpen(!isOpenNow);
      });
    }

    overlay.addEventListener("click", () => setOpen(false));

    const closeOnMobileNav = () => {
      if (!isDesktop()) setOpen(false);
    };

    qsa("#adminSidebar a[href^='/admin']").forEach((a) => {
      a.addEventListener("click", closeOnMobileNav);
    });
  };

  const initLogoutButtons = () => {
    const b1 = qs("#adminSidebarLogout");
    const b2 = qs("#adminHeaderLogout");
    if (b1) b1.addEventListener("click", logout);
    if (b2) b2.addEventListener("click", logout);
  };

  const formatMoney = (value) => {
    try {
      return Number(value).toLocaleString("ru-RU") + " ₽";
    } catch {
      return String(value) + " ₽";
    }
  };

  const renderNotifications = (orders) => {
    const body = qs("#adminNotifBody");
    if (!body) return;

    if (!orders.length) {
      body.className = "text-sm text-gray-600";
      body.textContent = "Нет новых уведомлений";
      return;
    }

    const wrap = document.createElement("div");
    wrap.className = "space-y-2";

    const header = document.createElement("div");
    header.className = "text-sm font-medium text-gray-900";
    header.textContent = `Новых заказов: ${orders.length}`;
    wrap.appendChild(header);

    orders.slice(0, 5).forEach((order) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "w-full rounded-lg border border-gray-200 p-2 text-left hover:bg-gray-50";
      btn.addEventListener("click", () => {
        const panel = qs("#adminNotifPanel");
        if (panel) panel.classList.add("hidden");
        window.location.href = `/admin/orders/${encodeURIComponent(order.id)}`;
      });

      const t1 = document.createElement("div");
      t1.className = "text-sm font-medium text-gray-900";
      t1.textContent = `Заказ #${String(order.id).slice(-6)}`;

      const t2 = document.createElement("div");
      t2.className = "text-xs text-gray-600";
      t2.textContent = `${order.customerName || ""} · ${formatMoney(order.totalAmount || 0)}`;

      btn.appendChild(t1);
      btn.appendChild(t2);
      wrap.appendChild(btn);
    });

    const allBtn = document.createElement("button");
    allBtn.type = "button";
    allBtn.className = "pt-1 text-sm text-blue-600 hover:text-blue-700";
    allBtn.textContent = "Открыть все заказы";
    allBtn.addEventListener("click", () => {
      const panel = qs("#adminNotifPanel");
      if (panel) panel.classList.add("hidden");
      window.location.href = "/admin/orders";
    });
    wrap.appendChild(allBtn);

    body.className = "";
    body.innerHTML = "";
    body.appendChild(wrap);
  };

  const initNotifications = () => {
    const btn = qs("#adminNotifBtn");
    const panel = qs("#adminNotifPanel");
    const close = qs("#adminNotifClose");
    const badge = qs("#adminNotifBadge");

    if (!btn || !panel || !badge) return;

    const loadOrders = async () => {
      try {
        const res = await fetch("/api/orders");
        if (!res.ok) return [];
        const data = await res.json();
        const orders = Array.isArray(data) ? data : [];
        const next = orders
          .filter((o) => o && o.status === "new")
          .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
        return next;
      } catch {
        return [];
      }
    };

    const update = async () => {
      const orders = await loadOrders();
      if (orders.length > 0) {
        badge.classList.remove("hidden");
        badge.classList.add("inline-flex");
        badge.textContent = orders.length > 9 ? "9+" : String(orders.length);
      } else {
        badge.classList.add("hidden");
        badge.classList.remove("inline-flex");
        badge.textContent = "";
      }
      renderNotifications(orders);
    };

    btn.addEventListener("click", async () => {
      const isHidden = panel.classList.contains("hidden");
      if (isHidden) {
        panel.classList.remove("hidden");
        await update();
      } else {
        panel.classList.add("hidden");
      }
    });

    if (close) {
      close.addEventListener("click", () => panel.classList.add("hidden"));
    }

    void update();
    window.setInterval(() => {
      void update();
    }, 30000);
  };

	  const showToast = (message, type = "info", duration = 3000) => {
	    if (typeof window.showToast === "function") {
	      window.showToast(message, type, { duration });
	    }
	  };

  const showConfirm = ({ title, message, confirmText = "Удалить" }) => {
    return new Promise((resolve) => {
      const root = document.createElement("div");
      root.className = "fixed inset-0 z-50 flex items-center justify-center";
      root.innerHTML = `
        <div class="fixed inset-0 bg-black/50"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
          <button type="button" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600" aria-label="Закрыть">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
            </svg>
          </button>
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008Z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a1.5 1.5 0 0 0 1.29 2.25h17.78A1.5 1.5 0 0 0 22.18 18L13.71 3.86a1.5 1.5 0 0 0-2.42 0Z"></path>
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-2"></h3>
              <p class="text-sm text-gray-600"></p>
            </div>
          </div>
          <div class="mt-6">
            <button type="button" data-confirm="1" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"></button>
          </div>
        </div>
      `;
      const h = root.querySelector("h3");
      const p = root.querySelector("p");
      const confirmBtn = root.querySelector("[data-confirm='1']");
      if (h) h.textContent = String(title || "");
      if (p) p.textContent = String(message || "");
      if (confirmBtn) confirmBtn.textContent = String(confirmText || "Удалить");

      const close = () => {
        root.remove();
        resolve(false);
      };
      root.querySelector(".fixed.inset-0")?.addEventListener("click", close);
      root.querySelector("button[aria-label='Закрыть']")?.addEventListener("click", close);
      confirmBtn?.addEventListener("click", () => {
        root.remove();
        resolve(true);
      });

      document.body.appendChild(root);
    });
  };

  const showModal = ({ title, body, actions }) => {
    const root = document.createElement("div");
    root.className = "fixed inset-0 bg-gray-100 backdrop-blur-xl flex items-center justify-center z-[9999] p-4";

    const widthClass = "max-w-md";
    root.innerHTML = `
      <div class="bg-white border border-gray-200 rounded-3xl w-full ${widthClass} shadow-xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center px-4 py-3 sm:py-4 bg-gray-50/50 border-b border-gray-100 flex-shrink-0">
          <h2 class="text-lg sm:text-xl font-semibold text-gray-900"></h2>
          <button type="button" class="text-gray-400 hover:text-gray-600 rounded-lg p-1 hover:bg-gray-300 transition-colors" aria-label="Закрыть">
            <svg class="h-5 w-5 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="overflow-y-auto flex-1 p-4 sm:p-6" data-body="1"></div>
        <div class="flex justify-end items-center gap-2 sm:gap-3 px-4 py-3 sm:py-4 bg-gray-50/50 border-t border-gray-100 flex-shrink-0" data-actions="1"></div>
      </div>
    `;

    const h2 = root.querySelector("h2");
    if (h2) h2.textContent = String(title || "");
    const bodyHost = root.querySelector("[data-body='1']");
    const actionsHost = root.querySelector("[data-actions='1']");
    if (bodyHost && body) bodyHost.appendChild(body);
    if (actionsHost && actions) actionsHost.appendChild(actions);

    const close = () => root.remove();
    root.addEventListener("click", close);
    root.querySelector("button[aria-label='Закрыть']")?.addEventListener("click", (e) => {
      e.stopPropagation();
      close();
    });
    root.querySelector("div.bg-white")?.addEventListener("click", (e) => e.stopPropagation());

    document.body.appendChild(root);
    return { close, root };
  };

  const jsonFromScript = (id) => {
    const el = document.getElementById(id);
    if (!el) return null;
    try {
      return JSON.parse(el.textContent || "null");
    } catch {
      return null;
    }
  };

  const apiJson = async (url, method, payload) => {
    const headers = { "Content-Type": "application/json" };
    let realMethod = method;
    let reqMethod = method;
    if (method === "PUT" || method === "DELETE") {
      reqMethod = "POST";
      headers["X-HTTP-Method-Override"] = method;
    }
    const res = await fetch(url, {
      method: reqMethod,
      headers,
      body: payload ? JSON.stringify(payload) : undefined,
    });
    const text = await res.text();
    let data = null;
    try {
      data = text ? JSON.parse(text) : null;
    } catch {
      data = null;
    }
    return { ok: res.ok, status: res.status, data, raw: text, method: realMethod };
  };

  const initCrudModals = () => {
    const parents = jsonFromScript("adminCategoryParents") || [];

    const openNameModal = ({ entity, mode, id, name = "", parentId = "" }) => {
      const titles = {
        color: mode === "edit" ? "Редактировать цвет" : "Добавить цвет",
        size: mode === "edit" ? "Редактировать размер" : "Добавить размер",
        category: mode === "edit" ? "Редактировать категорию" : "Добавить категорию",
      };

      const body = document.createElement("div");
      body.className = "space-y-4";

      const nameField = document.createElement("div");
      nameField.innerHTML = `
        <label class="block text-xs text-gray-600 mb-1">Название ${entity === "size" ? "размера" : entity === "color" ? "цвета" : "категории"} <span class="text-gray-500">*</span></label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-gray-300 focus:ring-0" value="">
      `;
      const nameInput = nameField.querySelector("input");
      if (nameInput) nameInput.value = String(name || "");
      body.appendChild(nameField);

      let parentSelect = null;
      if (entity === "category") {
        const parentField = document.createElement("div");
        const optionsHtml = [
          `<option value="">Без родителя</option>`,
          ...parents.map((p) => `<option value="${String(p.id)}">${String(p.name)}</option>`),
        ].join("");
        parentField.innerHTML = `
          <label class="block text-xs text-gray-600 mb-1">Родительская категория</label>
          <select class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-gray-300 focus:ring-0">${optionsHtml}</select>
        `;
        parentSelect = parentField.querySelector("select");
        if (parentSelect) parentSelect.value = String(parentId || "");
        body.appendChild(parentField);
      }

      const actions = document.createElement("div");
      actions.className = "flex gap-2 sm:gap-3 w-full";

      const saveBtn = document.createElement("button");
      saveBtn.type = "button";
      saveBtn.className = "flex-1 bg-blue-600 text-white border border-transparent hover:bg-blue-700 font-medium px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg";
      saveBtn.textContent = mode === "edit" ? "Сохранить" : "Создать";

      actions.appendChild(saveBtn);

      const modal = showModal({ title: titles[entity], body, actions });

      saveBtn.addEventListener("click", async () => {
        const nextName = (nameInput?.value || "").trim();
        if (!nextName) {
          showToast("Введите название", "warning");
          return;
        }
        let payload = { name: nextName };
        let url = "";
        let method = "POST";

        if (entity === "color") {
          url = mode === "edit" ? `/api/colors/${encodeURIComponent(id)}` : "/api/colors";
          method = mode === "edit" ? "PUT" : "POST";
        } else if (entity === "size") {
          url = mode === "edit" ? `/api/sizes/${encodeURIComponent(id)}` : "/api/sizes";
          method = mode === "edit" ? "PUT" : "POST";
        } else {
          const pid = parentSelect ? String(parentSelect.value || "") : "";
          payload = { ...payload, parentId: pid || null };
          url = mode === "edit" ? `/api/categories/${encodeURIComponent(id)}` : "/api/categories";
          method = mode === "edit" ? "PUT" : "POST";
        }

        saveBtn.disabled = true;
        const { ok, data } = await apiJson(url, method, payload);
        saveBtn.disabled = false;
        if (!ok) {
          const msg = (data && (data.error || data.message)) ? String(data.error || data.message) : "Ошибка сохранения";
          showToast(msg, "error");
          return;
        }
        modal.close();
        showToast(mode === "edit" ? "Сохранено" : "Создано", "success");
        window.setTimeout(() => window.location.reload(), 400);
      });
    };

    qsa("[data-crud-open]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const key = btn.getAttribute("data-crud-open") || "";
        const id = btn.getAttribute("data-id") || "";
        const name = btn.getAttribute("data-name") || "";
        const parentId = btn.getAttribute("data-parent-id") || "";
        if (key === "color-create") return openNameModal({ entity: "color", mode: "create" });
        if (key === "color-edit") return openNameModal({ entity: "color", mode: "edit", id, name });
        if (key === "size-create") return openNameModal({ entity: "size", mode: "create" });
        if (key === "size-edit") return openNameModal({ entity: "size", mode: "edit", id, name });
        if (key === "category-create") return openNameModal({ entity: "category", mode: "create" });
        if (key === "category-edit") return openNameModal({ entity: "category", mode: "edit", id, name, parentId });
      });
    });

    qsa("[data-crud-delete]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const entity = btn.getAttribute("data-crud-delete") || "";
        const id = btn.getAttribute("data-id") || "";
        if (!entity || !id) return;
        const ok = await showConfirm({
          title: `Удалить ${entity === "category" ? "категорию" : entity === "color" ? "цвет" : "размер"}?`,
          message: "Это действие нельзя отменить. Запись будет удалена навсегда.",
          confirmText: "Удалить",
        });
        if (!ok) return;

        let url = "";
        if (entity === "color") url = `/api/colors/${encodeURIComponent(id)}`;
        if (entity === "size") url = `/api/sizes/${encodeURIComponent(id)}`;
        if (entity === "category") url = `/api/categories/${encodeURIComponent(id)}`;
        if (!url) return;

        const res = await apiJson(url, "DELETE", null);
        if (!res.ok) {
          const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка удаления";
          showToast(msg, "error");
          return;
        }
        showToast("Удалено", "success");
        window.setTimeout(() => window.location.reload(), 400);
      });
    });
  };

	  const initProductDelete = () => {
	    qsa("[data-delete-product-id]").forEach((btn) => {
	      btn.addEventListener("click", async () => {
	        const id = btn.getAttribute("data-delete-product-id") || "";
	        if (!id) return;
	        const ok = await showConfirm({
	          title: "Удалить товар?",
	          message: "Это действие нельзя отменить. Товар будет удален навсегда.",
	          confirmText: "Удалить",
	        });
	        if (!ok) return;
	        try {
	          const res = await fetch(`/api/products/${encodeURIComponent(id)}`, {
	            method: "POST",
	            headers: { "X-HTTP-Method-Override": "DELETE" },
	          });
	          if (res.ok) {
	            showToast("Удалено", "success");
	            window.setTimeout(() => {
	              if (window.location.pathname.startsWith("/admin/products/")) {
	                window.location.href = "/admin/products";
	              } else {
	                window.location.reload();
	              }
	            }, 300);
	          } else {
	            showToast("Ошибка удаления товара", "error");
	          }
	        } catch {
	          showToast("Ошибка удаления товара", "error");
        }
      });
    });
  };

  const initProductForm = () => {
    const form = document.querySelector("form[data-product-form]");
    if (!form) return;

    const mode = form.getAttribute("data-product-mode") || "create";
    const productId = form.getAttribute("data-product-id") || "";
    const imagesValue = form.querySelector("[data-images-value]");
    const uploadInput = form.querySelector("[data-upload-images]");
    const imagesGrid =
      form.querySelector("[data-product-images-grid]") ||
      (uploadInput ? uploadInput.closest(".grid") : null);
    const imageMax = 6;

    let images = [];
    try {
      images = JSON.parse(imagesValue?.value || "[]");
      if (!Array.isArray(images)) images = [];
    } catch {
      images = [];
    }

    const isImageSelected = (url) => images.some((x) => String(x) === String(url));

    const syncImages = () => {
      if (imagesValue) imagesValue.value = JSON.stringify(images);
    };

    const removeImage = (url) => {
      images = images.filter((x) => String(x) !== String(url));
      syncImages();
      const el = imagesGrid?.querySelector(`[data-image-item="${CSS.escape(String(url))}"]`);
      if (el) el.remove();
      updateAddTile();
    };

    const updateAddTile = () => {
      if (!imagesGrid) return;
      const addTile = imagesGrid.querySelector("[data-upload-tile]");
      if (!addTile) return;
      if (images.length >= imageMax) {
        addTile.classList.add("hidden");
      } else {
        addTile.classList.remove("hidden");
      }
    };

    imagesGrid?.querySelectorAll("[data-remove-image]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const url = btn.getAttribute("data-remove-image") || "";
        if (url) removeImage(url);
      });
    });

    const addImageTile = (url) => {
      if (!imagesGrid) return;
      if (!url) return;
      if (images.length > imageMax) return;

      // Avoid duplicate tiles.
      if (imagesGrid.querySelector(`[data-image-item="${CSS.escape(String(url))}"]`)) return;

      const tile = document.createElement("div");
      tile.className = "relative bg-gray-100 rounded-lg overflow-hidden aspect-[3/4]";
      tile.setAttribute("data-image-item", String(url));
      tile.innerHTML = `
        <img src="${String(url)}" alt="Product" class="w-full h-full object-cover">
        <button
          type="button"
          data-remove-image="${String(url)}"
          class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-lg hover:bg-red-700"
          aria-label="Удалить"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
          </svg>
        </button>
      `;

      const btn = tile.querySelector("[data-remove-image]");
      if (btn) btn.addEventListener("click", () => removeImage(url));

      const addTile = imagesGrid.querySelector("[data-upload-tile]");
      if (addTile) {
        imagesGrid.insertBefore(tile, addTile);
      } else {
        imagesGrid.appendChild(tile);
      }
      updateAddTile();
    };

    if (uploadInput) {
      uploadInput.addEventListener("change", async () => {
        const files = Array.from(uploadInput.files || []);
        if (!files.length) return;

        for (const file of files) {
          if (images.length >= imageMax) break;
          if (!file || file.size === 0) continue;
          const fd = new FormData();
          fd.append("file", file);
          fd.append("folder", "products");

          try {
            const res = await fetch("/api/upload", { method: "POST", body: fd });
            const data = await res.json().catch(() => null);
            if (!res.ok) {
              const msg = data?.error ? String(data.error) : "Ошибка загрузки изображения";
              showToast(msg, "error");
              continue;
            }
            const url = data?.url ? String(data.url) : "";
            if (!url) continue;
            if (isImageSelected(url)) continue;
            images.push(url);
            syncImages();
            addImageTile(url);
          } catch {
            showToast("Ошибка загрузки изображения", "error");
          }
        }

        uploadInput.value = "";
      });
    }

    const selectedBtnClasses = ["bg-blue-600", "border-blue-600", "text-white"];
    const unselectedBtnClasses = ["bg-white", "border-gray-300", "text-gray-700"];

    const setToggleBtnSelected = (btn, selected) => {
      if (!btn) return;
      if (selected) {
        unselectedBtnClasses.forEach((c) => btn.classList.remove(c));
        btn.classList.add(...selectedBtnClasses);
        btn.setAttribute("aria-pressed", "true");
      } else {
        selectedBtnClasses.forEach((c) => btn.classList.remove(c));
        btn.classList.add(...unselectedBtnClasses);
        btn.setAttribute("aria-pressed", "false");
      }
    };

    const isToggleBtnSelected = (btn) => {
      if (!btn) return false;
      if (btn.getAttribute("aria-pressed") === "true") return true;
      return btn.classList.contains("bg-blue-600") || btn.classList.contains("border-blue-600");
    };

    // Colors / sizes toggle buttons.
    form.querySelectorAll("[data-toggle-color]").forEach((btn) => {
      btn.addEventListener("click", () => setToggleBtnSelected(btn, !isToggleBtnSelected(btn)));
    });
    form.querySelectorAll("[data-toggle-size]").forEach((btn) => {
      btn.addEventListener("click", () => setToggleBtnSelected(btn, !isToggleBtnSelected(btn)));
    });

    // In-stock switch.
    const instockWrap = form.querySelector("[data-instock]");
    const instockBtn = form.querySelector("[data-toggle-instock]");
    const instockDot = form.querySelector("[data-instock-dot]");
    const setInStock = (value) => {
      const v = Boolean(value);
      if (instockWrap) instockWrap.setAttribute("data-instock", v ? "1" : "0");
      if (instockBtn) {
        instockBtn.classList.toggle("bg-blue-600", v);
        instockBtn.classList.toggle("bg-gray-200", !v);
      }
      if (instockDot) {
        instockDot.classList.toggle("translate-x-6", v);
        instockDot.classList.toggle("translate-x-1", !v);
      }
    };
    if (instockBtn) {
      instockBtn.addEventListener("click", () => {
        const current = (instockWrap?.getAttribute("data-instock") || "1") === "1";
        setInStock(!current);
      });
    }

    updateAddTile();

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(form);

      const name = String(fd.get("name") || "").trim();
      const category = String(fd.get("category") || "").trim();
      const article = String(fd.get("article") || "").trim();
      const price = Number(fd.get("price") || 0);
      const stock = Number(fd.get("stock") || 0);
      const description = String(fd.get("description") || "").trim();
      const material = String(fd.get("material") || "").trim();
      const careRaw = String(fd.get("care") || "");
      const inStock = (instockWrap?.getAttribute("data-instock") || "1") === "1";

      if (!name || !category || !article || !Number.isFinite(price) || !material || !description) {
        showToast("Заполните обязательные поля", "warning");
        return;
      }

      let imgList = images;
      if (!Array.isArray(imgList)) imgList = [];

      const colors = Array.from(form.querySelectorAll("[data-toggle-color]"))
        .filter((btn) => isToggleBtnSelected(btn))
        .map((btn) => String(btn.getAttribute("data-toggle-color") || "").trim())
        .filter(Boolean);

      const sizes = Array.from(form.querySelectorAll("[data-toggle-size]"))
        .filter((btn) => isToggleBtnSelected(btn))
        .map((btn) => String(btn.getAttribute("data-toggle-size") || "").trim())
        .filter(Boolean);

      const care = String(careRaw || "")
        .split(/[\n,]+/g)
        .map((c) => c.trim())
        .filter(Boolean);

      const payload = {
        name,
        category,
        article,
        price: Number(price) || 0,
        stock: Number.isFinite(stock) ? Number(stock) : 0,
        images: imgList,
        colors,
        sizes,
        description,
        material,
        care,
        inStock,
      };

      const submitBtn = form.querySelector("button[type='submit']");
      if (submitBtn) submitBtn.disabled = true;

      const url = mode === "edit" ? `/api/products/${encodeURIComponent(productId)}` : "/api/products";
      const method = mode === "edit" ? "PUT" : "POST";
      const res = await apiJson(url, method, payload);

      if (submitBtn) submitBtn.disabled = false;

      if (!res.ok) {
        const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка сохранения товара";
        showToast(msg, "error");
        return;
      }

      showToast(mode === "edit" ? "Товар обновлен" : "Товар создан", "success");
      window.setTimeout(() => {
        window.location.href = "/admin/products";
      }, 700);
    });
  };

  const initOrderDetails = () => {
    const host = qs("[data-order-details]");
    if (!host) return;
    const orderId = host.getAttribute("data-order-id") || "";
    const statusSelect = host.querySelector("[data-order-status]");
    const saveBtn = host.querySelector("[data-order-save]");
    if (!orderId || !statusSelect || !saveBtn) return;

    saveBtn.addEventListener("click", async () => {
      const status = String(statusSelect.value || "").trim();
      if (!status) {
        showToast("Выберите статус", "warning");
        return;
      }

      const prevText = saveBtn.textContent || "";
      saveBtn.disabled = true;
      saveBtn.textContent = "Сохранение...";
      const res = await apiJson(`/api/orders/${encodeURIComponent(orderId)}`, "PUT", { status });
      saveBtn.disabled = false;
      saveBtn.textContent = prevText;

      if (!res.ok) {
        const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка сохранения";
        showToast(msg, "error");
        return;
      }
      showToast("Статус сохранен", "success");
      window.setTimeout(() => window.location.reload(), 500);
    });
  };

  const initOrderDelete = () => {
    qsa("[data-delete-order-id]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.getAttribute("data-delete-order-id") || "";
        if (!id) return;
        const ok = await showConfirm({
          title: "Удалить заказ?",
          message: "Это действие нельзя отменить. Заказ будет удален навсегда.",
          confirmText: "Удалить",
        });
        if (!ok) return;

        btn.disabled = true;
        const res = await apiJson(`/api/orders/${encodeURIComponent(id)}`, "DELETE", null);
        btn.disabled = false;
        if (!res.ok) {
          const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка удаления заказа";
          showToast(msg, "error");
          return;
        }
        showToast("Заказ удален", "success");
        window.setTimeout(() => window.location.reload(), 500);
      });
    });
  };

  const initUserEdit = () => {
    const form = qs("form[data-user-edit-form]");
    if (!form) return;
    const userId = form.getAttribute("data-user-id") || "";
    const userType = form.getAttribute("data-user-type") || "";
    const isAdmin = userType === "admin";

    const deleteBtn = qs("[data-user-delete]");
    const saveBtn = qs("[data-user-save]");

    const setDisabled = (disabled) => {
      form.querySelectorAll("input, select, textarea, button").forEach((el) => {
        if (el === deleteBtn) return;
        if (el === saveBtn) return;
        if (el.getAttribute("type") === "button") return;
        el.disabled = Boolean(disabled);
      });
    };

    if (!isAdmin) {
      setDisabled(true);
    }

    // Password visibility toggles (eye icon).
    form.querySelectorAll("[data-password-toggle]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const wrap = btn.closest("[data-password-field]");
        const input = wrap ? wrap.querySelector("[data-password-input]") : null;
        if (!input) return;
        const isHidden = input.getAttribute("type") === "password";
        input.setAttribute("type", isHidden ? "text" : "password");
        btn.setAttribute("aria-label", isHidden ? "Скрыть пароль" : "Показать пароль");
      });
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!isAdmin) return;
      if (!userId) return;

      const fd = new FormData(form);
      const name = String(fd.get("name") || "").trim();
      const email = String(fd.get("email") || "").trim();
      const phone = String(fd.get("phone") || "").trim();
      const address = String(fd.get("address") || "").trim();
      const role = String(fd.get("role") || "").trim();
      const password = String(fd.get("password") || "");
      const confirmPassword = String(fd.get("confirmPassword") || "");

      if (!name || !email || !role) {
        showToast("Заполните имя, email и роль", "warning");
        return;
      }
      if (password && password !== confirmPassword) {
        showToast("Пароли не совпадают", "error");
        return;
      }

      const payload = { name, email, phone, address, role };
      if (password) payload.password = password;

      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = "Сохранение...";
      }
      const res = await apiJson(`/api/users/${encodeURIComponent(userId)}`, "PUT", payload);
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = "Сохранить";
      }

      if (!res.ok) {
        const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка сохранения пользователя";
        showToast(msg, "error");
        return;
      }
      showToast("Пользователь обновлен", "success");
      window.setTimeout(() => (window.location.href = "/admin/users"), 600);
    });

    if (deleteBtn) {
      deleteBtn.addEventListener("click", async () => {
        if (!isAdmin) return;
        if (!userId) return;
        const ok = await showConfirm({
          title: "Удалить пользователя?",
          message: "Это действие нельзя отменить.",
          confirmText: "Удалить",
        });
        if (!ok) return;

        deleteBtn.disabled = true;
        const res = await apiJson(`/api/users/${encodeURIComponent(userId)}`, "DELETE", null);
        deleteBtn.disabled = false;

        if (!res.ok) {
          const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка удаления пользователя";
          showToast(msg, "error");
          return;
        }
        showToast("Пользователь удален", "success");
        window.setTimeout(() => (window.location.href = "/admin/users"), 600);
      });
    }
  };

  const initSettings = () => {
    const form = qs("form[data-settings-form]");
    if (!form) return;

    const tabButtons = qsa("[data-settings-tab-btn]");
    const panes = qsa("[data-settings-tab-pane]");
    const searchInput = qs("[data-settings-search]");
    const setTab = (key) => {
      tabButtons.forEach((btn) => {
        const k = btn.getAttribute("data-settings-tab-btn");
        const active = k === key;
        btn.classList.toggle("bg-blue-600", active);
        btn.classList.toggle("text-white", active);
        btn.classList.toggle("bg-white", !active);
        btn.classList.toggle("text-gray-700", !active);
      });
      panes.forEach((pane) => {
        const k = pane.getAttribute("data-settings-tab-pane");
        pane.classList.toggle("hidden", k !== key);
      });

      // Re-apply search filter on tab switch.
      if (searchInput) {
        try {
          applySettingsSearch(String(searchInput.value || ""));
        } catch {
          // ignore
        }
      }
    };

    const defaultTab = form.getAttribute("data-settings-default-tab") || "contacts";
    setTab(defaultTab);
    tabButtons.forEach((btn) => {
      btn.addEventListener("click", () => setTab(btn.getAttribute("data-settings-tab-btn") || "contacts"));
    });

    const getActivePane = () => panes.find((p) => !p.classList.contains("hidden")) || null;

    const applySettingsSearch = (query) => {
      const pane = getActivePane();
      if (!pane) return;
      const q = String(query || "").trim().toLowerCase();
      const groups = Array.from(pane.querySelectorAll("div")).filter((d) => d.querySelector("label"));

      if (!q) {
        groups.forEach((g) => (g.style.display = ""));
        return;
      }

      groups.forEach((g) => {
        const label = g.querySelector("label");
        const text = String(label?.textContent || "").toLowerCase();
        g.style.display = text.includes(q) ? "" : "none";
      });
    };

    if (searchInput) {
      searchInput.addEventListener("input", () => applySettingsSearch(String(searchInput.value || "")));
    }

    const initImageList = (key) => {
      const host = qs(`[data-settings-images='${key}']`);
      const jsonInput = qs(`[data-settings-images-json='${key}']`);
      const upload = host?.querySelector("[data-settings-upload]") || null;
      const grid = upload ? upload.closest(".grid") : null;
      const maxImages = 4;

      let images = [];
      try {
        images = JSON.parse(jsonInput?.value || "[]");
        if (!Array.isArray(images)) images = [];
      } catch {
        images = [];
      }

      const sync = () => {
        if (jsonInput) jsonInput.value = JSON.stringify(images);
      };

      const updateAddTile = () => {
        const addTile = grid?.querySelector("[data-settings-upload-tile]");
        if (!addTile) return;
        addTile.classList.toggle("hidden", images.length >= maxImages);
      };

      const remove = (url) => {
        images = images.filter((x) => String(x) !== String(url));
        sync();
        const el = grid?.querySelector(`[data-image-item="${CSS.escape(String(url))}"]`);
        if (el) el.remove();
        updateAddTile();
      };

      grid?.querySelectorAll("[data-remove-image]").forEach((btn) => {
        btn.addEventListener("click", () => {
          const url = btn.getAttribute("data-remove-image") || "";
          if (url) remove(url);
        });
      });

      const addTile = (url) => {
        if (!grid) return;
        if (!url) return;
        if (grid.querySelector(`[data-image-item="${CSS.escape(String(url))}"]`)) return;

        const tile = document.createElement("div");
        tile.className = "relative bg-gray-100 rounded-lg overflow-hidden aspect-[3/4]";
        tile.setAttribute("data-image-item", String(url));
        tile.innerHTML = `
          <img src="${String(url)}" alt="Slide" class="w-full h-full object-cover">
          <button
            type="button"
            data-remove-image="${String(url)}"
            class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-lg hover:bg-red-700"
            aria-label="Удалить"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
            </svg>
          </button>
        `;
        tile.querySelector("[data-remove-image]")?.addEventListener("click", () => remove(url));

        const add = grid.querySelector("[data-settings-upload-tile]");
        if (add) grid.insertBefore(tile, add);
        else grid.appendChild(tile);
        updateAddTile();
      };

      if (upload) {
        upload.addEventListener("change", async () => {
          const files = Array.from(upload.files || []);
          if (!files.length) return;

          for (const file of files) {
            if (images.length >= maxImages) break;
            if (!file || file.size === 0) continue;
            const fd = new FormData();
            fd.append("file", file);
            fd.append("folder", key === "slider1" ? "sliders/slider-1" : "sliders/slider-2");

            try {
              const res = await fetch("/api/upload", { method: "POST", body: fd });
              const data = await res.json().catch(() => null);
              if (!res.ok) {
                const msg = data?.error ? String(data.error) : "Ошибка загрузки изображения";
                showToast(msg, "error");
                continue;
              }
              const url = data?.url ? String(data.url) : "";
              if (!url) continue;
              if (images.some((x) => String(x) === url)) continue;
              images.push(url);
              sync();
              addTile(url);
            } catch {
              showToast("Ошибка загрузки изображения", "error");
            }
          }

          upload.value = "";
        });
      }

      updateAddTile();
      return { getImages: () => images.slice() };
    };

    const slider1 = initImageList("slider1");
    const slider2 = initImageList("slider2");

    const parseKey = (key) => {
      const base = String(key || "").split("[")[0];
      const parts = [base];
      const re = /\[([^\]]*)\]/g;
      let m;
      while ((m = re.exec(String(key))) !== null) {
        parts.push(m[1]);
      }
      return parts.filter((p) => p !== "");
    };

    const setNested = (obj, parts, value) => {
      let cur = obj;
      for (let i = 0; i < parts.length; i += 1) {
        const key = parts[i];
        const isLast = i === parts.length - 1;
        const nextKey = parts[i + 1];
        const keyIsIndex = String(Number(key)) === key;
        const nextIsIndex = String(Number(nextKey)) === nextKey;

        if (isLast) {
          if (keyIsIndex) {
            if (!Array.isArray(cur)) return;
            cur[Number(key)] = value;
          } else {
            cur[key] = value;
          }
          return;
        }

        if (keyIsIndex) {
          if (!Array.isArray(cur)) return;
          if (cur[Number(key)] == null) cur[Number(key)] = nextIsIndex ? [] : {};
          cur = cur[Number(key)];
        } else {
          if (cur[key] == null) cur[key] = nextIsIndex ? [] : {};
          cur = cur[key];
        }
      }
    };

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      const payload = {};

      // Basic fields.
      payload.phone = String(fd.get("phone") || "").trim();
      payload.email = String(fd.get("email") || "").trim();
      payload.whatsapp = String(fd.get("whatsapp") || "").trim();
      payload.telegram = String(fd.get("telegram") || "").trim();

      payload.slider1Images = slider1 ? slider1.getImages() : [];
      payload.slider2Images = slider2 ? slider2.getImages() : [];

      // Nested fields from bracket notation.
      for (const [key, value] of fd.entries()) {
        const k = String(key || "");
        if (!k.includes("[")) continue;
        const parts = parseKey(k);
        if (!parts.length) continue;
        setNested(payload, parts, String(value || ""));
      }

      const submitBtn = qs("[data-settings-save]");
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Сохранение...";
      }
      const res = await apiJson("/api/settings", "PUT", payload);
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = "Сохранить";
      }

      if (!res.ok) {
        const msg = (res.data && (res.data.error || res.data.message)) ? String(res.data.error || res.data.message) : "Ошибка сохранения настроек";
        showToast(msg, "error");
        return;
      }
      showToast("Настройки сохранены", "success");
      window.setTimeout(() => window.location.reload(), 500);
    });
  };

  const initTableSearch = () => {
    const input = qs("[data-table-search-input]");
    if (!input) return;
    const rows = qsa("[data-table-search-row]");

    const apply = () => {
      const q = String(input.value || "").trim().toLowerCase();
      if (!q) {
        rows.forEach((r) => (r.style.display = ""));
        return;
      }
      const terms = q.split(/\s+/).filter(Boolean);
      rows.forEach((r) => {
        const text = String(r.getAttribute("data-search-text") || "").toLowerCase();
        const ok = terms.every((t) => text.includes(t));
        r.style.display = ok ? "" : "none";
      });
    };

    input.addEventListener("input", apply);
  };

  const initLoginPasswordToggle = () => {
    const btn = qs("[data-toggle-password]");
    const input = qs("[data-password-input]");
    if (!btn || !input) return;
    btn.addEventListener("click", () => {
      const next = input.getAttribute("type") === "password" ? "text" : "password";
      input.setAttribute("type", next);
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    if (!document.querySelector("[data-admin-shell='1']")) {
      initLoginPasswordToggle();
      return;
    }
    initSidebar();
    initLogoutButtons();
    initNotifications();
    initCrudModals();
    initProductForm();
    initProductDelete();
    initOrderDetails();
    initOrderDelete();
    initUserEdit();
    initSettings();
    initTableSearch();
  });
})();
