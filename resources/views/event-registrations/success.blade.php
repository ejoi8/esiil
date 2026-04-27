<x-layouts.public
    :title="'Pendaftaran Diterima'"
    :heading="'Pendaftaran Diterima'"
    :description="'Halaman pengesahan selepas pendaftaran program berjaya diterima melalui pautan jemputan.'"
    :robots="'noindex,nofollow,noarchive'"
    :canonical="false"
>
    <div class="mx-auto grid w-full max-w-4xl min-w-0 gap-6">
        <section class="min-w-0 rounded-[2rem] border border-text/10 bg-surface/95 p-6 shadow-2xl shadow-text/10 backdrop-blur sm:p-8 lg:p-10">
            <div class="space-y-8">
                <div class="space-y-4">
                    <span class="inline-flex rounded-full bg-accent/45 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-primary">
                        Selesai
                    </span>

                    <div class="min-w-0 space-y-3">
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-primary">Status Pendaftaran</p>
                        <h2 class="font-display text-3xl font-semibold leading-tight text-text sm:text-4xl">
                            Pendaftaran berjaya diterima.
                        </h2>
                        <p class="max-w-2xl text-sm leading-7 text-text/65">
                            Sijil tidak dijana semasa pendaftaran supaya proses kekal laju ketika trafik tinggi. Klik butang muat turun apabila anda benar-benar mahu menjana sijil.
                        </p>
                    </div>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Nama</dt>
                        <dd class="mt-2 font-semibold text-text">{{ $registration->participant->full_name }}</dd>
                    </div>
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">No. KP</dt>
                        <dd class="mt-2 font-semibold text-text">{{ $registration->participant->nokp }}</dd>
                    </div>
                    <div class="rounded-[1.35rem] border border-text/10 bg-white/75 p-4 sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-[0.22em] text-text/65">Program</dt>
                        <dd class="mt-2 font-semibold text-text">{{ $registration->event->title }}</dd>
                    </div>
                </dl>

                <div class="rounded-[1.5rem] border border-primary/15 bg-primary/5 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary">Muat Turun Sijil</p>
                        <p class="mt-2 text-sm leading-6 text-text/65">
                        PDF akan dijana di server setiap kali anda muat turun sijil ini.
                        </p>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        @if ($registration->certificate_type !== null)
                            <a
                                href="{{ route('events.register.certificate', $registration) }}"
                                class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-bold uppercase tracking-[0.16em] text-white shadow-lg shadow-primary/20 transition hover:bg-text focus:outline-none focus:ring-4 focus:ring-primary/20"
                            >
                                Muat Turun Sijil
                            </a>
                        @endif

                        <a
                            href="{{ route('certificate-lookup.index') }}"
                            class="inline-flex items-center justify-center rounded-full border border-text/15 bg-surface px-5 py-3 text-sm font-semibold text-text shadow-sm shadow-text/5 transition hover:border-primary/40 hover:text-primary focus:outline-none focus:ring-4 focus:ring-primary/15"
                        >
                            Semakan Sijil
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
