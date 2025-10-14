@if($result->isNotEmpty())
@foreach($result AS $row)
<div onclick="setEmployee({{ $row->id_user }},'{{ $row->nama }}','{{ $row->nik }}')" class="cursor-pointer w-100 card shadow mb-2 mt-2 px-5 py-3 hover-card-employee">
    <span class="fs-5">{{ $row->nama }}</span>
    <span class="text-muted fs-7">NIK : {{ $row->nik }}</span>
</div>
@endforeach
@else
<span class="py-4 text-danger">
    <i class="fa-solid fa-xmark text-danger me-3"></i>
    <span class="text-daanger">Tidak Ada Karyawan Ditemukan</span>
</span>
@endif