@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])

<div class="row mt20">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="uk-flex uk-flex-middle uk-flex-space-between">
                    <h5>{{ $config['seo']['index']['table'] }}</h5>
                </div>
            </div>
            <div class="ibox-content">

                <form action="{{ route('eligibility.option.index') }}">
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
                                    <select name="eligibility_question_id" class="form-control setupSelect2 mr10">
                                        <option value="">[Tất cả câu hỏi]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ request('eligibility_question_id') == $cha->id ? 'selected' : '' }}>{{ $cha->name ?? $cha->question }}</option>
                                        @endforeach
                                    </select>                                    @include('backend.dashboard.component.keyword')
                                    <a href="{{ route('eligibility.option.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i>Thêm mới</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th>Đáp án</th>
                            <th style="width:260px;">Thuộc câu hỏi</th>
                            <th class="text-center" style="width:140px;">Kết luận</th>
                            <th class="text-center" style="width:80px;">Điểm</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($options as $o)
                            <tr>
                                    <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                    <td>{{ $o->label }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($o->question->question ?? '—', 60) }}</td>
                                    <td class="text-center"><span class="label label-{{ $o->verdict === 'pass' ? 'success' : ($o->verdict === 'fail' ? 'danger' : 'warning') }}">{{ $o->tenKetLuan() }}</span></td>
                                    <td class="text-center">{{ $o->score }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('eligibility.option.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                        <a href="{{ route('eligibility.option.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                    </td>
                            </tr>
                        @endforeach

                        @if(!$options->count())
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding:30px;">
                                    Chưa có bản ghi nào. Bấm <strong>Thêm mới</strong> để bắt đầu.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $options->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
