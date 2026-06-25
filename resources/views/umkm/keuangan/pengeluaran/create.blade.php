<x-umkm-layout header="Catat Pengeluaran">
    <div class="max-w-3xl bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" action="{{ route('umkm.pengeluaran.store') }}" class="space-y-4">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis Pengeluaran</label>
                    <select name="jenis_pengeluaran_id" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="">— pilih —</option>
                        @foreach ($jenisList as $j)<option value="{{ $j->id }}">{{ $j->nama }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal</label>
                    <input type="date" name="tanggal_pengeluaran" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 text-sm">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium">Rincian Item</label>
                    <button type="button" onclick="addRow()" class="text-sm text-emerald-600">+ Tambah baris</button>
                </div>
                <table class="w-full text-sm" id="itemsTable">
                    <thead class="text-left text-gray-500">
                        <tr><th class="py-1">Keterangan</th><th class="py-1 w-20">Qty</th><th class="py-1 w-32">Harga</th><th class="py-1 w-32 text-right">Subtotal</th><th></th></tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                    <tfoot><tr><td colspan="3" class="text-right font-medium py-2">Total</td><td class="text-right font-bold py-2" id="grandTotal">Rp0</td><td></td></tr></tfoot>
                </table>
            </div>

            <div class="flex gap-2">
                <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm">Simpan</button>
                <a href="{{ route('umkm.pengeluaran.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>

    <script>
        let idx = 0;
        function rupiah(n){ return 'Rp' + (n||0).toLocaleString('id-ID'); }
        function addRow(){
            const tb = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-1 pr-2"><input name="items[${idx}][keterangan]" required class="w-full rounded-md border-gray-300 text-sm"></td>
                <td class="py-1 pr-2"><input name="items[${idx}][qty]" type="number" min="1" value="1" oninput="recalc()" class="w-full rounded-md border-gray-300 text-sm qty"></td>
                <td class="py-1 pr-2"><input name="items[${idx}][harga]" type="number" min="0" value="0" oninput="recalc()" class="w-full rounded-md border-gray-300 text-sm harga"></td>
                <td class="py-1 text-right sub">Rp0</td>
                <td class="py-1 text-right"><button type="button" onclick="this.closest('tr').remove();recalc()" class="text-red-600 text-xs">×</button></td>`;
            tb.appendChild(tr); idx++;
        }
        function recalc(){
            let total = 0;
            document.querySelectorAll('#itemsBody tr').forEach(tr => {
                const q = +tr.querySelector('.qty').value || 0;
                const h = +tr.querySelector('.harga').value || 0;
                const s = q*h; total += s;
                tr.querySelector('.sub').textContent = rupiah(s);
            });
            document.getElementById('grandTotal').textContent = rupiah(total);
        }
        addRow();
    </script>
</x-umkm-layout>
