<x-layouts.public
    :title="'Semakan Sijil'"
    :description="'Semak sijil program PUSPANITA menggunakan nombor kad pengenalan dan muat turun sijil digital yang tersedia secara terus.'"
    :canonical="route('certificate-lookup.index')"
>
    <div class="mx-auto grid w-full max-w-3xl min-w-0 gap-6">
        <section class="min-w-0 rounded-[2rem] border border-text/10 bg-surface/95 p-6 shadow-2xl shadow-text/10 backdrop-blur sm:p-8 lg:p-10">
            <div class="space-y-7">
                <div class="space-y-4">
                    <span class="inline-flex rounded-full bg-accent/45 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-primary">
                        Langkah 01
                    </span>
                    <div class="min-w-0 space-y-3">
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-primary">Carian Peserta</p>
                        <h2 class="font-display text-3xl font-semibold text-text">Masukkan No. KP</h2>
                        <p class="max-w-xl text-sm leading-7 text-text/65">
                            Masukkan nombor kad pengenalan tanpa simbol tambahan. Keputusan akan dipaparkan di halaman seterusnya jika rekod ditemui.
                        </p>
                    </div>
                </div>

                <form action="{{ route('certificate-lookup.search') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="nokp" class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">No. KP</label>
                        <input
                            id="nokp"
                            name="nokp"
                            type="text"
                            value="{{ old('nokp') }}"
                            placeholder="Contoh: 900101015555"
                            class="w-full rounded-[1.15rem] border border-text/10 bg-white px-4 py-3.5 text-base text-text shadow-sm shadow-text/5 outline-none transition placeholder:text-text/40 focus:border-primary focus:ring-4 focus:ring-primary/15"
                            required
                        >
                        @error('nokp')
                            <p class="rounded-xl bg-danger/10 px-3 py-2 text-sm font-semibold text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 border-t border-text/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm leading-6 text-text/65">Semakan dihadkan untuk mengelakkan penggunaan berulang secara berlebihan.</p>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-bold uppercase tracking-[0.16em] text-white shadow-lg shadow-primary/20 transition hover:bg-text focus:outline-none focus:ring-4 focus:ring-primary/20"
                        >
                            Semak Sijil
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-layouts.public>
