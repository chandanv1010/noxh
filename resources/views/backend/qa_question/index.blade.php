@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])

<div class="row mt20">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="uk-flex uk-flex-middle uk-flex-space-between">
                    <h5>{{ $config['seo']['index']['table'] }}</h5>
                    @include('backend.dashboard.component.toolbox', ['model' => $config['model']])
                </div>
            </div>
            <div class="ibox-content">

                <div class="row mb15">
                    @foreach($trangThai as $ma => $ten)
                        <div class="col-lg-3 col-sm-6">
                            <a href="{{ route('qa.question.index', ['status' => $ma]) }}"
                               class="btn btn-block btn-outline {{ request('status') === $ma ? 'btn-primary' : 'btn-default' }}"
                               style="white-space:normal;margin-bottom:10px;">
                                {{ $ten }}<br><strong>{{ $demTheoTrangThai[$ma] ?? 0 }} câu</strong>
                            </a>
                        </div>
                    @endforeach
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ route('qa.question.index') }}"
                           class="btn btn-block btn-outline {{ request('status') ? 'btn-default' : 'btn-primary' }}"
                           style="white-space:normal;margin-bottom:10px;">
                            Tất cả<br><strong>{{ array_sum($demTheoTrangThai) }} câu</strong>
                        </a>
                    </div>
                </div>

                <form action="{{ route('qa.question.index') }}">
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
                                    @include('backend.dashboard.component.filterPublish')
                                    @include('backend.dashboard.component.keyword')
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-bordered mt20">
                    <thead>
                        <tr>
                            <th style="width:50px;"><input type="checkbox" value="" id="checkAll" class="input-checkbox"></th>
                            <th>Câu hỏi</th>
                            <th style="width:170px;">Người hỏi</th>
                            <th style="width:130px;" class="text-center">Trạng thái</th>
                            <th style="width:90px;" class="text-center">Trả lời</th>
                            <th style="width:140px;">Ngày gửi</th>
                            <th class="text-center" style="width:100px;">Hiển thị</th>
                            <th class="text-center" style="width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questions as $o)
                            <tr>
                                <td><input type="checkbox" value="{{ $o->id }}" class="input-checkbox checkBoxItem"></td>
                                <td>
                                    <span class="text-success">{{ \Illuminate\Support\Str::limit($o->title, 110) }}</span>
                                    @if($o->is_featured)<span class="label label-warning ml5">Nổi bật</span>@endif
                                </td>
                                <td>
                                    {{ $o->asker_name ?: '—' }}
                                    @if($o->asker_phone)<br><small class="text-muted">{{ $o->asker_phone }}</small>@endif
                                </td>
                                <td class="text-center"><span class="label label-{{ $o->mauTrangThai() }}">{{ $o->tenTrangThai() }}</span></td>
                                <td class="text-center">{{ $o->answers_count }}</td>
                                <td>{{ $o->created_at ? $o->created_at->format('H:i d/m/Y') : '' }}</td>
                                <td class="text-center js-switch-{{ $o->id }}">
                                    <input type="checkbox" value="{{ $o->publish }}" class="js-switch status" data-field="publish" data-model="{{ $config['model'] }}" {{ ($o->publish == 2) ? 'checked' : '' }} data-modelId="{{ $o->id }}" />
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('qa.question.edit', $o->id) }}" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('qa.question.delete', $o->id) }}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        @if(!$questions->count())
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                    Chưa có câu hỏi nào. Câu hỏi sẽ tự xuất hiện khi bạn đọc gửi từ website.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $questions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
