// Form transaksi: tampilkan field sesuai jenis transaksi
(function () {
  var type = document.getElementById('type');
  if (!type) return;
  function sync() {
    var t = type.value, tr = t === 'transfer';
    document.querySelectorAll('.tr-only').forEach(function (el) { el.style.display = tr ? '' : 'none'; });
    document.querySelectorAll('.cat-only').forEach(function (el) { el.style.display = tr ? 'none' : ''; });
    var sel = document.getElementById('category_id');
    Array.prototype.forEach.call(sel.options, function (o) {
      if (!o.value) return;
      var ok = o.dataset.type === t;
      o.hidden = !ok; o.disabled = !ok;
      if (!ok && o.selected) sel.value = '';
    });
  }
  type.addEventListener('change', sync); sync();
})();
