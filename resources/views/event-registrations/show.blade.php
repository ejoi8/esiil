<x-layouts.public
    :title="$event->title"
    :heading="'Pendaftaran Program'"
    :description="trim(collect([
        $event->description,
        $event->venue ? 'Lokasi: '.$event->venue : null,
        $event->starts_at?->format('d M Y') ? 'Tarikh: '.$event->starts_at?->format('d M Y') : null,
    ])->filter()->implode(' | ')) ?: 'Pendaftaran program PUSPANITA melalui pautan jemputan yang dikongsi kepada peserta.'"
    :robots="'noindex,nofollow,noarchive'"
    :canonical="false"
>
    <div class="grid w-full max-w-full min-w-0 gap-6 lg:grid-cols-[0.92fr_1.08fr] lg:gap-8">
        <section class="min-w-0 rounded-[2rem] border border-text/10 bg-surface/90 p-6 shadow-2xl shadow-text/10 backdrop-blur sm:p-8 lg:p-10">
            <div class="space-y-8">
                <div class="space-y-4">
                    <span class="inline-flex rounded-full bg-accent/45 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-primary">
                        Langkah 01
                    </span>

                    <div class="min-w-0 space-y-3">
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-primary">Program</p>
                        <h2 class="font-display text-3xl font-semibold leading-tight text-text sm:text-4xl">{{ $event->title }}</h2>

                        @if ($event->description)
                            <p class="max-w-xl text-sm leading-7 text-text/65">{{ $event->description }}</p>
                        @endif
                    </div>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Tarikh</dt>
                        <dd class="mt-2 font-semibold text-text">{{ $event->starts_at?->format('d M Y') }}</dd>
                    </div>
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Masa</dt>
                        <dd class="mt-2 font-semibold text-text">
                            {{ $event->start_time_text ?: $event->starts_at?->format('g:i A') }}
                            @if ($event->end_time_text)
                                - {{ $event->end_time_text }}
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Lokasi</dt>
                        <dd class="mt-2 font-semibold text-text">{{ $event->venue ?: 'Lokasi tidak dinyatakan' }}</dd>
                    </div>
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Anjuran</dt>
                        <dd class="mt-2 font-semibold text-text">{{ $event->organizer_name }}</dd>
                    </div>
                </dl>

                <div class="rounded-[1.5rem] border border-primary/15 bg-primary/5 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary">Semakan Sijil</p>
                    <p class="mt-2 text-sm leading-6 text-text/65">Sudah hadir program? Semak sijil menggunakan nombor kad pengenalan yang sama.</p>
                    <a
                        href="{{ route('certificate-lookup.index') }}"
                        class="mt-4 inline-flex items-center justify-center rounded-full border border-text/15 bg-surface px-4 py-2 text-sm font-semibold text-text shadow-sm shadow-text/5 transition hover:border-primary/40 hover:text-primary focus:outline-none focus:ring-4 focus:ring-primary/15"
                    >
                        Semakan Sijil
                    </a>
                </div>
            </div>
        </section>

        <section class="min-w-0 rounded-[2rem] border border-text/10 bg-surface/95 p-6 shadow-2xl shadow-text/10 backdrop-blur sm:p-8 lg:p-10">
            <div class="space-y-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 space-y-2">
                        <span class="inline-flex rounded-full bg-accent/45 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-primary">
                            Langkah 02
                        </span>
                        <div>
                            <h2 class="font-display text-2xl font-semibold text-text">Maklumat Peserta</h2>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-text/65">Isi maklumat dengan tepat supaya rekod pendaftaran dan sijil boleh dipadankan kemudian.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @if (session('registration_created'))
                        <div class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            Pendaftaran berjaya diterima.
                        </div>
                    @endif

                    @if (session('registration_exists'))
                        <div class="rounded-[1.25rem] border border-accent bg-accent/35 px-4 py-3 text-sm font-semibold text-primary">
                            Rekod pendaftaran untuk No. KP ini telah wujud.
                        </div>
                    @endif

                    @if ($errors->has('event'))
                        <div class="rounded-[1.25rem] border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
                            {{ $errors->first('event') }}
                        </div>
                    @endif

                    @if (! $registrationIsOpen)
                        <div class="rounded-[1.25rem] border border-text/10 bg-background px-4 py-3 text-sm font-semibold text-text/65">
                            Pendaftaran belum dibuka atau telah ditutup.
                        </div>
                    @endif
                </div>

                <form action="{{ request()->fullUrl() }}" method="POST" class="space-y-6" data-registration-form>
                    @csrf

                    <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <label for="full_name" class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Nama Penuh</label>
                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                value="{{ old('full_name') }}"
                                placeholder="Nama seperti dalam kad pengenalan"
                                class="w-full rounded-[1.15rem] border border-text/10 bg-white px-4 py-3.5 text-base text-text shadow-sm shadow-text/5 outline-none transition placeholder:text-text/40 focus:border-primary focus:ring-4 focus:ring-primary/15"
                                required
                            >
                            @error('full_name')
                                <p class="rounded-xl bg-danger/10 px-3 py-2 text-sm font-semibold text-danger">{{ $message }}</p>
                            @enderror
                        </div>

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

                        <div class="space-y-2">
                            <label for="email" class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Emel</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                class="w-full rounded-[1.15rem] border border-text/10 bg-white px-4 py-3.5 text-base text-text shadow-sm shadow-text/5 outline-none transition placeholder:text-text/40 focus:border-primary focus:ring-4 focus:ring-primary/15"
                                required
                            >
                            @error('email')
                                <p class="rounded-xl bg-danger/10 px-3 py-2 text-sm font-semibold text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Telefon</label>
                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                value="{{ old('phone') }}"
                                placeholder="Nombor telefon"
                                class="w-full rounded-[1.15rem] border border-text/10 bg-white px-4 py-3.5 text-base text-text shadow-sm shadow-text/5 outline-none transition placeholder:text-text/40 focus:border-primary focus:ring-4 focus:ring-primary/15"
                            >
                            @error('phone')
                                <p class="rounded-xl bg-danger/10 px-3 py-2 text-sm font-semibold text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="membership_status" class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Status Ahli</label>
                            <div class="relative">
                                <select
                                    id="membership_status"
                                    name="membership_status"
                                    class="w-full appearance-none rounded-[1.15rem] border border-text/10 bg-white px-4 py-3.5 pr-11 text-base text-text shadow-sm shadow-text/5 outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15"
                                    required
                                >
                                    <option value="member" @selected(old('membership_status') === 'member')>Ahli</option>
                                    <option value="non_member" @selected(old('membership_status', 'non_member') === 'non_member')>Bukan Ahli</option>
                                </select>
                                <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-text/65" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            @error('membership_status')
                                <p class="rounded-xl bg-danger/10 px-3 py-2 text-sm font-semibold text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="flex flex-col gap-3 border-t border-text/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm leading-6 text-text/65">Tekan hantar sekali sahaja. Jika rekod telah wujud, mesej status akan dipaparkan.</p>
                        <button
                            type="submit"
                            @disabled(! $registrationIsOpen)
                            data-submit-button
                            class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-bold uppercase tracking-[0.16em] text-white shadow-lg shadow-primary/20 transition hover:bg-text focus:outline-none focus:ring-4 focus:ring-primary/20 disabled:cursor-not-allowed disabled:bg-text/10 disabled:text-text/65 disabled:shadow-none"
                        >
                            <span data-submit-label>Hantar Pendaftaran</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-registration-form]');
            const submitButton = form?.querySelector('[data-submit-button]');
            const submitLabel = submitButton?.querySelector('[data-submit-label]');

            if (! form || ! submitButton || ! submitLabel) {
                return;
            }

            form.addEventListener('submit', (event) => {
                if (submitButton.disabled) {
                    event.preventDefault();

                    return;
                }

                submitButton.disabled = true;
                submitButton.setAttribute('aria-disabled', 'true');
                submitLabel.textContent = 'Sedang Dihantar...';
            });
        });
    </script>
</x-layouts.public>
