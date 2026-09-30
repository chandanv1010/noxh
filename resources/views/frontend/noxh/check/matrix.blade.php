{{--
    Khoi dap an chia theo TINH HUONG (ban ve noxh_image/w-2.jpg).

    Moi tinh huong la mot tam mau rieng co tranh, ten va dong ghi chu; cac muc
    chon nam trong tam tuong ung. Ca ba tam dung CHUNG mot nhom radio: thu
    nhap cua mot nguoi chi roi vao mot truong hop, chon o tam nay la bo chon o
    tam kia.
--}}
@php
    $tam = $cau->optionGroups;

    // Dap an chua xep vao tam nao van phai hien ra, neu khong quan tri doi bo
    // cuc sang "chia theo tinh huong" la mat tich vai dap an ma khong hieu vi
    // sao.
    $ngoai = $cau->options->whereNull('eligibility_option_group_id');
@endphp

<div class="nx-wz-tam">
    @foreach($tam as $nhom)
        @php
            [$nen, $net] = $nhom->mauHinh();
            $anh = trim((string) $nhom->image);
            $hinh = \App\Classes\NoxhIcon::hopLe($nhom->icon) ? $nhom->icon : '';
        @endphp

        <div class="nx-wz-tam__o" style="--nx-nen: {{ $nen }}; --nx-net: {{ $net }}">
            <div class="nx-wz-tam__dau">
                @if($anh !== '')
                    <img class="nx-wz-tam__anh" src="{{ $anh }}" alt="" loading="lazy">
                @elseif($hinh)
                    <span class="nx-wz-tam__hinh">
                        @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 44])
                    </span>
                @endif

                <span class="nx-wz-tam__chu">
                    <strong>{{ $nhom->label }}</strong>
                    @if(trim((string) $nhom->note) !== '')
                        <small>{{ $nhom->note }}</small>
                    @endif
                </span>
            </div>

            <div class="nx-wz-tam__ds">
                @foreach($nhom->options as $da)
                    @include('frontend.noxh.check.choice', ['da' => $da, 'daChon' => $daChon])
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@if($ngoai->count())
    <div class="nx-wz-tam__le">
        @foreach($ngoai as $da)
            @include('frontend.noxh.check.choice', ['da' => $da, 'daChon' => $daChon])
        @endforeach
    </div>
@endif
