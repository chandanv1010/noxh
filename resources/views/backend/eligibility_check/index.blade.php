@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])

<div class="row mt20">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ $config['seo']['index']['table'] }}</h5>
            </div>
            <div class="ibox-content">

                <div class="row mb15">
                    @foreach($muc as $ma => $ten)
                        <div class="col-lg-3 col-sm-6">
                            <a href="{{ route('eligibility.check.index', ['result_level' => $ma]) }}"
                               class="btn btn-block btn-outline {{ request('result_level') === $ma ? 'btn-primary' : 'btn-default' }}"
                               style="white-space:normal;margin-bottom:10px;">
                                {{ $ten }}<br><strong>{{ $demTheoMuc[$ma] ?? 0 }} lượt</strong>
                            </a>
                        </div>
                    @endforeach
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ route('eligibility.check.index') }}"
                           class="btn btn-block btn-outline {{ request('result_level') ? 'btn-default' : 'btn-primary' }}"
                           style="white-space:normal;margin-bottom:10px;">
                            Tất cả<br><strong>{{ array_sum($demTheoMuc) }} lượt</strong>
                        </a>
                    </div>
                </div>

                <form action="{{ route('eligibility.check.index') }}">
                    <div class="filter-wrapper">
                        <div class="uk-flex uk-flex-middle uk-flex-space-between">
                            <div class="perpage">
                                @php $perpage = request('perpage') ?: old('perpage'); @endphp
                                <select name="perpage" class="form-control input-sm perpage filter mr10">
                                    @for($i = 20; $i <= 200; $i += 20)
                                        <option {{ ($perpage == $i) ? 'selected' : '' }} value="{{ $i }}">{{ $i }} bản ghi</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="action">
                                <div class="uk-flex uk-flex-middle">
                                    @include('backend.dashboard.component.keyword')
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:170px;">Mã tra cứu</th>
                            <th style="width:170px;">Họ và tên</th>
                            <th style="width:130px;">Điện thoại</th>
                            <th style="width:90px;" class="text-center">Điểm</th>
                            <th style="width:150px;" class="text-center">Kết luận</th>
                            <th style="width:150px;" class="text-center">Đạt / Cần kiểm tra</th>
                            <th style="width:150px;">Thời điểm</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checks as $o)
                            <tr>
                                <td>
                                    <span class="text-success">{{ $o->code }}</span>
                                    @unless($o->conHan())
                                        <br><small class="text-muted">Đã quá hạn 30 ngày</small>
                                    @endunless
                                </td>
                                <td>{{ $o->name ?: '—' }}</td>
                                <td>@if($o->phone)<a href="tel:{{ $o->phone }}">{{ $o->phone }}</a>@else — @endif</td>
                                <td class="text-center"><strong>{{ $o->score_percent }}%</strong></td>
                                <td class="text-center">
                                    <span class="label label-{{ $o->mauMuc() }}">{{ $o->tenMuc() }}</span>
                                </td>
                                <td class="text-center">{{ $o->passed }} / {{ $o->unclear }}</td>
                                <td>{{ $o->created_at ? $o->created_at->format('H:i d/m/Y') : '' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('eligibility.check.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('eligibility.check.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        @if(!$checks->count())
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                    Chưa có lượt kiểm tra nào. Bản ghi sẽ tự xuất hiện khi khách làm bài kiểm tra ngoài website.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $checks->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
