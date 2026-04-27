<x-layouts.public
    :title="'Keputusan Semakan Sijil'"
    :heading="'Keputusan Semakan Sijil'"
    :description="'Halaman keputusan semakan sijil untuk peserta yang telah membuat carian menggunakan nombor kad pengenalan.'"
    :robots="'noindex,nofollow,noarchive'"
    :canonical="false"
>
    <div class="mx-auto grid w-full max-w-5xl min-w-0 gap-6">
        <section class="min-w-0 rounded-[2rem] border border-text/10 bg-surface/90 p-6 shadow-2xl shadow-text/10 backdrop-blur sm:p-8 lg:p-10">
            <div class="space-y-6">
                <div class="flex flex-col gap-4 rounded-[1.6rem] border border-primary/15 bg-accent/25 p-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0 space-y-1">
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-primary">Peserta</p>
                        <h2 class="font-display text-3xl font-semibold text-text">{{ $participant->full_name }}</h2>
                        <p class="text-sm text-text/65">No. KP: {{ $participant->nokp }}</p>
                        <p class="text-sm text-text/65">Emel: {{ $participant->email }}</p>
                    </div>

                    <div class="rounded-[1.25rem] bg-text px-5 py-4 text-white shadow-lg shadow-text/15">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-white/70">Jumlah Rekod</p>
                        <p class="mt-1 font-display text-3xl font-semibold">{{ $registrations->count() }}</p>
                    </div>
                </div>

                <div class="max-w-full overflow-hidden rounded-[2rem] border border-text/10 bg-surface">
                    <div class="overflow-x-auto">
                        <table class="min-w-[560px] w-full table-fixed border-collapse divide-y divide-text/10 bg-surface text-left">
                            <thead class="bg-background text-left text-xs font-bold uppercase tracking-[0.22em] text-text/65">
                                <tr>
                                    <th class="px-4 py-4">Program</th>
                                    <th class="px-4 py-4">Tarikh</th>
                                    <th class="px-4 py-4">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-text/10 text-sm text-text/65">
                                @forelse ($registrations as $registration)
                                    <tr class="align-top">
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-text">{{ $registration->event->title }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-text/65">
                                            {{ $registration->event->starts_at?->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($registration->certificate_type !== null)
                                                <a
                                                    href="{{ route('certificate-lookup.download', $registration) }}"
                                                    class="inline-flex items-center justify-center rounded-full bg-text px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-primary focus:outline-none focus:ring-4 focus:ring-primary/20"
                                                >
                                                    Muat Turun
                                                </a>
                                            @else
                                                <span class="text-xs font-semibold text-text/45">Tiada sijil</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-text/65">
                                            Tiada rekod sijil dijumpai untuk peserta ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a
                        href="{{ route('certificate-lookup.index') }}"
                        class="inline-flex items-center justify-center rounded-full border border-text/15 bg-surface px-4 py-2 text-sm font-semibold text-text shadow-sm shadow-text/5 transition hover:border-primary/40 hover:text-primary focus:outline-none focus:ring-4 focus:ring-primary/15"
                    >
                        Buat Semakan Baru
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
