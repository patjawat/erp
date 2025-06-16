(function (global) {
  function floatype(l, e = {}) {
    const o = {
      onQuery: null,
      onNavigate: null,
      onSelect: null,
      onRender: null,
      debounce: 100,
      ...e,
    };

    let s, a = 0, i = [], t, n;

    const r = (function () {
      var e = document.createElement("div"),
        t = (document.querySelector("body").appendChild(e), window.getComputedStyle(l));
      for (const n of t) e.style[n] = t[n];
      return e.style.visibility = "hidden", e.style.position = "absolute", e.style.left = "-500%", e.style.top = "-500%", e;
    })();

    function u(e) {
      if (e.type === "keydown") {
        if (!s) return;
        switch (e.keyCode) {
          case 38: return d(-1, e), false;
          case 40: return d(1, e), false;
          case 9:
          case 32:
          case 13: e.preventDefault(); f(a); return void p();
          case 27: return p(), true;
        }
      }
      if (e.type !== "blur") {
        let val = l.value.substring(0, l.selectionStart);
        if (/\S$/.test(val)) {
          t = val.match(/\S*$/)[0];
          clearTimeout(n);
          n = setTimeout(c, o.debounce);
        } else {
          p();
        }
      }
    }

    async function c() {
      if (!t) return;
      i = await o.onQuery(t);
      if (!i.length) return p();

      if (!s) {
        s = document.createElement("div");
        Object.assign(s.style, {
          width: window.getComputedStyle(l).width,
          position: "fixed",
          left: l.offsetLeft + "px",
          top: l.offsetTop + l.offsetHeight + "px"
        });
        s.classList.add("floatype");
        l.parentNode.insertBefore(s, l.nextSibling);
      }

      s.innerHTML = "";

      const caretPos = (function (e) {
        const value = e.value.substring(0, e.selectionStart);
        const index = Math.max(value.lastIndexOf("\n"), value.lastIndexOf(" ")) + 1;
        const caretId = "floatype-caret";
        r.innerHTML = e.value.substring(0, index) + `<span id="${caretId}" style="display:inline-block;"></span>`;
        const span = document.getElementById(caretId);
        const rect = e.getBoundingClientRect();
        return {
          x: rect.left + span.offsetLeft - e.scrollLeft,
          y: rect.top + span.offsetTop - e.scrollTop + (parseInt(getComputedStyle(span).height) + 5),
        };
      })(l);

      s.style.left = caretPos.x + "px";
      s.style.top = caretPos.y + "px";

      i.forEach((item, idx) => {
        const div = document.createElement("div");
        div.classList.add("floatype-item");
        if (o.onRender) {
          div.appendChild(o.onRender(item));
        } else {
          div.innerText = item;
        }
        if (idx === a) div.classList.add("floatype-sel");
        div.addEventListener("mousedown", () => f(idx));
        s.appendChild(div);
      });
    }

    function d(e, t) {
      t.preventDefault();
      const old = s.querySelector(`:nth-child(${a + 1})`);
      if (old) old.classList.remove("floatype-sel");
      a = (a + e + i.length) % i.length;
      const newSel = s.querySelector(`:nth-child(${a + 1})`);
      if (newSel) newSel.classList.add("floatype-sel");
    }

    function f(index) {
      const val = o.onSelect ? o.onSelect(i[index]) : i[index];
      const tval = l;
      const cursorStart = Math.max(tval.value.lastIndexOf(" ", tval.selectionStart - 1), tval.value.lastIndexOf("\n", tval.selectionStart - 1)) + 1;
      tval.value = tval.value.substring(0, cursorStart) + val + (tval.value[tval.selectionStart] !== " " ? " " : "") + tval.value.substring(tval.selectionStart);
      tval.setSelectionRange(cursorStart + val.length + 1, cursorStart + val.length + 1);
      setTimeout(() => l.focus(), 50);
    }

    function p() {
      i = [];
      a = 0;
      t = null;
      if (s) {
        s.remove();
        s = null;
      }
    }

    ["input", "keydown", "blur"].forEach(ev => l.addEventListener(ev, u));

    return {
      unbind: function () {
        ["input", "keydown", "blur"].forEach(ev => l.removeEventListener(ev, u));
        p();
        r.remove();
      }
    };
  }

  // 👇 กำหนดเป็น Global ตัวแปร
  global.floatype = floatype;
})(window);
