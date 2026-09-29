{{--
    Duong tien do du an - danh sach moc theo thoi gian.

    Dung o ba cho: khoi nho canh ban do (4 moc dau), tab "Tien do" va hop bat
    len khi bam "Xem cap nhat tien do". Khac nhau chi o so moc truyen vao va
    co ve them mo ta / anh hay khong.

    Tham so: $moc (collection ProjectMilestone), $coAnh (bool)
--}}
@php $coAnh = $coAnh ?? false; @endphp

<ol class="nx-pd-moc{{ $coAnh ? ' nx-pd-moc--day' : '' }}">
    @foreach($moc as $m)
        <li class="{{ $m->status === 'done' ? 'is-xong' : ($m->status === 'doing' ? 'is-lam' : '') }}">
            <span class="nx-pd-moc__dau">
                @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 18])
            </span>

            <span class="nx-pd-moc__chu">
                <strong>{{ $m->date_label ?: ($m->sort_date ? nx_quy_nam($m->sort_date) : '') }}</strong>
                <span>{{ $m->title }}</span>

                @if($coAnh && $m->description)
                    <span class="nx-pd-moc__mo">{{ $m->description }}</span>
                @endif

                @if($coAnh && $m->image)
                    <img src="{{ $m->image }}" alt="{{ $m->title }}" loading="lazy" decoding="async">
                @endif
            </span>
        </li>
    @endforeach
</ol>
