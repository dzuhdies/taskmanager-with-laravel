<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Manager — Aktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-slate-50 text-slate-900">
    <main class="max-w-md mx-auto p-4 pb-28">
        <h1 class="text-xl font-bold mb-3">Tugas Aktif</h1>

        <form class="flex gap-2 mb-3" method="GET">
            <input name="q" value="{{ request('q') }}" class="flex-1 rounded border p-2" placeholder="Cari judul/deskripsi...">
            <button class="px-3 py-2 rounded bg-slate-900 text-white">Cari</button>
        </form>

        <ul id="taskList" class="bg-white rounded-2xl shadow divide-y">
            @forelse($tasks as $task)
            <li id="task-{{ $task->id }}" class="flex items-center gap-3 p-3">
                <input type="checkbox" aria-label="Tandai selesai"
                    class="w-5 h-5 accent-emerald-600"
                    onchange="toggleStatus({{ $task->id }}, this)">
                <button class="flex-1 text-left"
                    onclick="openSheet({{ $task->id }}, '{{ e($task->title) }}', `{{ e($task->description) }}`)">
                    <div class="font-medium">{{ $task->title }}</div>
                </button>
            </li>
            @empty
            <li class="p-4 text-center text-slate-500">Belum ada tugas aktif.</li>
            @endforelse
        </ul>

        <div class="mt-4">{{ $tasks->links() }}</div>
    </main>

    <nav class="fixed bottom-0 inset-x-0 bg-white border-t shadow z-40">
        <div class="max-w-md mx-auto grid grid-cols-2 text-xs">
            <a href="{{ route('tasks.index') }}" class="flex flex-col items-center py-2 text-blue-600">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Aktif</span>
            </a>
            <a href="{{ route('tasks.history') }}" class="flex flex-col items-center py-2 text-slate-600">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>History</span>
            </a>
        </div>
    </nav>

    <button onclick="openAddModal()"
        class="fixed bottom-20 right-5 w-14 h-14 rounded-full bg-blue-600 text-white grid place-items-center shadow z-50"
        aria-label="Tambah Task">
        <span class="text-3xl leading-none">+</span>
    </button>

    <div id="addModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeAddModal()"></div>
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Tambah Tugas</h3>
                <button class="text-slate-600" onclick="closeAddModal()">Tutup</button>
            </div>
            <form id="formAdd" action="{{ route('tasks.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm mb-1">Judul</label>
                    <input name="title" required class="w-full rounded border p-2" placeholder="Judul tugas">
                </div>
                <div>
                    <label class="block text-sm mb-1">Deskripsi (opsional)</label>
                    <textarea name="description" rows="3" class="w-full rounded border p-2" placeholder="Catatan singkat..."></textarea>
                </div>
                <button class="w-full px-4 py-2 rounded bg-blue-600 text-white">Simpan</button>
            </form>
        </div>
    </div>

    <div id="detailSheet" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeSheet()"></div>
        <div id="sheetPanel" class="absolute inset-x-0 bottom-0 translate-y-full transition-transform duration-300">
            <div class="mx-auto max-w-md bg-white rounded-t-2xl p-4 shadow">
                <div class="w-12 h-1.5 bg-slate-300 rounded mx-auto mb-3"></div>
                <div id="shTitle" class="font-semibold mb-1"></div>
                <div id="shDesc" class="text-slate-600 whitespace-pre-wrap mb-4"></div>
                <div class="flex justify-end gap-2">
                    <button id="shDeleteBtn" class="px-3 py-2 rounded bg-rose-600 text-white">Hapus</button>
                    <button class="px-3 py-2 rounded bg-slate-200" onclick="closeSheet()">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function escapeHtml(s) {
            return (s || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [m]))
        }

        async function csrfFetch(url, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const headers = Object.assign({
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }, options.headers || {});
            return fetch(url, {
                ...options,
                headers
            });
        }

        async function toggleStatus(id, el) {
            const res = await csrfFetch(`/tasks/${id}/toggle`, {
                method: 'PATCH'
            });
            if (!res.ok) {
                alert('Gagal update');
                el && (el.checked = !el.checked);
                return;
            }
            const data = await res.json();
            if (data.is_done) document.getElementById(`task-${id}`)?.remove(); // pindah ke History
        }

        async function deleteTask(id) {
            const res = await csrfFetch(`/tasks/${id}`, {
                method: 'DELETE'
            });
            if (!res.ok) {
                alert('Gagal hapus');
                return;
            }
            document.getElementById(`task-${id}`)?.remove();
            closeSheet();
        }

        function rowHtml(t) {
            return `
        <li id="task-${t.id}" class="flex items-center gap-3 p-3">
          <input type="checkbox" class="w-5 h-5 accent-emerald-600" onchange="toggleStatus(${t.id}, this)">
          <button class="flex-1 text-left" onclick="openSheet(${t.id}, '${escapeHtml(t.title)}', \`${escapeHtml(t.description||'')}\`)">
            <div class="font-medium">${escapeHtml(t.title)}</div>
          </button>
        </li>`;
        }

        document.getElementById('formAdd').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const res = await csrfFetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });
            if (!res.ok) {
                alert('Gagal menambah');
                return;
            }
            const {
                task
            } = await res.json();
            document.getElementById('taskList').insertAdjacentHTML('afterbegin', rowHtml(task));
            form.reset();
            closeAddModal();
        });

        let sheetId = null;

        function openSheet(id, title, desc) {
            sheetId = id;
            document.getElementById('shDesc').textContent = desc || '(Tidak ada deskripsi)';
            document.getElementById('shDeleteBtn').onclick = () => deleteTask(sheetId);

            const wrap = document.getElementById('detailSheet');
            const panel = document.getElementById('sheetPanel');
            wrap.classList.remove('hidden');
            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-full');
            });
            document.body.style.overflow = 'hidden';
        }

        function closeSheet() {
            const wrap = document.getElementById('detailSheet');
            const panel = document.getElementById('sheetPanel');
            panel.classList.add('translate-y-full');
            setTimeout(() => {
                wrap.classList.add('hidden');
                document.body.style.overflow = '';
            }, 200);
            sheetId = null;
        }

        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>
</body>

</html>