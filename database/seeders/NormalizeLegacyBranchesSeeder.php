<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Participant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NormalizeLegacyBranchesSeeder extends Seeder
{
    public function run(): void
    {
        $aliases = [
            'KEMENTERIAN ALAM SEKITAR DAN AIR' => 'KEMENTERIAN SUMBER ASLI DAN KELESTARIAN ALAM',
            'KEMENTERIAN HAL EHWAL EKONOMI' => 'KEMENTERIAN EKONOMI',
            'KEMENTERIAN KOMUNIKASI DAN DIGITAL' => 'KEMENTERIAN KOMUNIKASI',
            'KEMENTERIAN KOMUNIKASI DAN MULTIMEDIA' => 'KEMENTERIAN KOMUNIKASI',
            'KEMENTERIAN PEMBANGUNAN DAN KERAJAAN TEMPATAN' => 'KEMENTERIAN PEMBANGUNAN KERAJAAN TEMPATAN',
            'KEMENTERIAN PEMBANGUNAN LUAR BANDAR' => 'KEMENTERIAN KEMAJUAN DESA DAN WILAYAH',
            'KEMENTERIAN PENGAJIAN TINGGI' => 'KEMENTERIAN PENDIDIKAN TINGGI',
            'KEMENTERIAN PERDAGANGAN ANTARABANGSA DAN INDUSTRI' => 'KEMENTERIAN PELABURAN, PERDAGANGAN DAN INDUSTRI',
            'KEMENTERIAN PERDAGANGAN DALAM NEGERI & HAL EHWAL PENGGUNA' => 'KEMENTERIAN PERDAGANGAN DALAM NEGERI DAN KOS SARA HIDUP',
            'KEMENTERIAN PERTANIAN DAN INDUSTRI MAKANAN' => 'KEMENTERIAN PERTANIAN DAN KETERJAMINAN MAKANAN',
            'KEMENTERIAN PERUMAHAN DAN KERAJAAN TEMPATAN' => 'KEMENTERIAN PEMBANGUNAN KERAJAAN TEMPATAN',
            'KEMENTERIAN PERUSAHAAN PERLADANGAN DAN KOMODITI' => 'KEMENTERIAN PERLADANGAN DAN KOMODITI',
            'KEMENTERIAN SUMBER ASLI, ALAM SEKITAR DAN PERUBAHAN IKLIM (NRECC)' => 'KEMENTERIAN SUMBER ASLI DAN KELESTARIAN ALAM',
            'KEMENTERIAN TENAGA DAN SUMBER ASLI' => 'KEMENTERIAN PERALIHAN TENAGA DAN DAYA GUNA AWAM',
        ];

        $branchesByName = Branch::query()
            ->get()
            ->keyBy(fn (Branch $branch): string => $this->normalizeKey($branch->name));

        foreach ($aliases as $alias => $canonical) {
            $aliasBranch = $branchesByName->get($this->normalizeKey($alias));
            $canonicalBranch = $branchesByName->get($this->normalizeKey($canonical));

            if ($aliasBranch === null || $canonicalBranch === null || $aliasBranch->is($canonicalBranch)) {
                continue;
            }

            Participant::query()
                ->where('branch_id', $aliasBranch->id)
                ->update(['branch_id' => $canonicalBranch->id]);

            $aliasBranch->forceDelete();
        }

        $reserveBranch = $branchesByName->get($this->normalizeKey('RESERVE'));

        if ($reserveBranch !== null) {
            Participant::query()
                ->where('branch_id', $reserveBranch->id)
                ->update(['branch_id' => null]);

            $reserveBranch->forceDelete();
        }

        Branch::withTrashed()
            ->whereIn('name', array_merge(array_keys($aliases), ['RESERVE']))
            ->get()
            ->each(fn (Branch $branch) => $branch->forceDelete());
    }

    protected function normalizeKey(string $value): string
    {
        return Str::lower(trim(preg_replace('/\s+/', ' ', $value)));
    }
}
