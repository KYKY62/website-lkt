@extends('admin.layout', ['title' => 'Manajemen Menu'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Manajemen Menu</h1>
            <p>Atur menu utama, submenu, sumber tujuan, dan sekarang urutannya bisa disusun dengan drag and drop.</p>
        </div>

        <div class="button-row">
            <button type="button" class="button button--secondary" id="save-menu-order">Simpan Urutan</button>
            <a href="{{ route('admin.menus.create') }}" class="button button--primary">Tambah Menu</a>
        </div>
    </section>

    <section class="card">
        <div class="card__body">
            @if ($menus->isEmpty())
                <div class="empty-state">Belum ada menu website tersimpan.</div>
            @else
                <div class="toolbar">
                    <div>
                        <strong>{{ $menus->count() }}</strong> master menu tersimpan
                    </div>
                    <div style="color: var(--ink-soft); font-size: 0.9rem;">Seret kartu menu untuk mengubah urutan. Submenu diseret di dalam grup induknya masing-masing.</div>
                </div>

                <div class="menu-sort" id="menu-tree">
                    @foreach ($menus as $menu)
                        <article class="menu-sort__item" draggable="true" data-menu-id="{{ $menu->id }}">
                            <div class="menu-sort__item-header">
                                <div>
                                    <div class="menu-sort__title">{{ $menu->label }}</div>
                                    <div class="menu-sort__meta">
                                        {{ $menu->item_type }} | {{ $menu->page?->path ?? $menu->url ?? ($menu->module_key ? \App\Models\SiteMenu::modulePath($menu->module_key) : '-') }}
                                    </div>
                                </div>
                                <div class="button-row">
                                    <span class="menu-sort__handle">Drag</span>
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="button button--ghost">Edit</a>
                                </div>
                            </div>

                            <div class="menu-sort__children" data-children-list>
                                @forelse ($menu->children as $child)
                                    <div class="menu-sort__child" draggable="true" data-menu-id="{{ $child->id }}">
                                        <div class="menu-sort__child-header">
                                            <div>
                                                <div class="menu-sort__title">{{ $child->label }}</div>
                                                <div class="menu-sort__meta">
                                                    submenu | {{ $child->item_type }} | {{ $child->page?->path ?? $child->url ?? ($child->module_key ? \App\Models\SiteMenu::modulePath($child->module_key) : '-') }}
                                                </div>
                                            </div>
                                            <div class="button-row">
                                                <span class="menu-sort__handle">Drag</span>
                                                <a href="{{ route('admin.menus.edit', $child) }}" class="button button--ghost">Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="menu-sort__empty">Belum ada submenu.</div>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <script>
        (() => {
            const menuTree = document.querySelector('#menu-tree');
            const saveButton = document.querySelector('#save-menu-order');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let draggedElement = null;

            if (!menuTree || !saveButton) {
                return;
            }

            const clearEmptyStates = () => {
                menuTree.querySelectorAll('.menu-sort__children').forEach((childrenList) => {
                    const items = childrenList.querySelectorAll('.menu-sort__child');
                    let emptyState = childrenList.querySelector('.menu-sort__empty');

                    if (!items.length) {
                        if (!emptyState) {
                            emptyState = document.createElement('div');
                            emptyState.className = 'menu-sort__empty';
                            emptyState.textContent = 'Belum ada submenu.';
                            childrenList.appendChild(emptyState);
                        }
                    } else if (emptyState) {
                        emptyState.remove();
                    }
                });
            };

            const getAfterElement = (container, y, selector) => {
                const elements = [...container.querySelectorAll(selector + ':not(.is-dragging)')];

                return elements.reduce((closest, element) => {
                    const box = element.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;

                    if (offset < 0 && offset > closest.offset) {
                        return { offset, element };
                    }

                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
            };

            const bindDraggableItems = () => {
                menuTree.querySelectorAll('.menu-sort__item, .menu-sort__child').forEach((item) => {
                    item.addEventListener('dragstart', () => {
                        draggedElement = item;
                        item.classList.add('is-dragging');
                    });

                    item.addEventListener('dragend', () => {
                        item.classList.remove('is-dragging');
                        draggedElement = null;
                        clearEmptyStates();
                    });
                });
            };

            menuTree.addEventListener('dragover', (event) => {
                event.preventDefault();

                if (!draggedElement) {
                    return;
                }

                const targetChildren = event.target.closest('[data-children-list]');

                if (draggedElement.classList.contains('menu-sort__child') && targetChildren) {
                    const afterElement = getAfterElement(targetChildren, event.clientY, '.menu-sort__child');

                    if (afterElement) {
                        targetChildren.insertBefore(draggedElement, afterElement);
                    } else {
                        targetChildren.appendChild(draggedElement);
                    }

                    clearEmptyStates();
                    return;
                }

                if (draggedElement.classList.contains('menu-sort__item')) {
                    const afterElement = getAfterElement(menuTree, event.clientY, '.menu-sort__item');

                    if (afterElement) {
                        menuTree.insertBefore(draggedElement, afterElement);
                    } else {
                        menuTree.appendChild(draggedElement);
                    }
                }
            });

            saveButton.addEventListener('click', async () => {
                const tree = [...menuTree.querySelectorAll('.menu-sort__item')].map((item) => ({
                    id: Number(item.dataset.menuId),
                    children: [...item.querySelectorAll('[data-children-list] > .menu-sort__child')].map((child) => ({
                        id: Number(child.dataset.menuId),
                    })),
                }));

                saveButton.disabled = true;
                saveButton.textContent = 'Menyimpan...';

                try {
                    const response = await fetch('{{ route('admin.menus.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({ tree }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        window.alert(payload.message ?? 'Gagal menyimpan urutan menu.');
                        return;
                    }

                    window.location.reload();
                } catch (error) {
                    window.alert('Server belum dapat dihubungi saat menyimpan urutan menu.');
                } finally {
                    saveButton.disabled = false;
                    saveButton.textContent = 'Simpan Urutan';
                }
            });

            bindDraggableItems();
            clearEmptyStates();
        })();
    </script>
@endsection
