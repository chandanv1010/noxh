@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method']]['title']])
@include('backend.dashboard.component.formError')
@php
    $url = ($config['method'] == 'create') ? route('product.store') : route('product.update', [$product->id, $queryUrl ?? '']);
@endphp
<form action="{{ $url }}" method="post" class="box nx-form-du-an">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>{{ __('messages.tableHeading') }}</h5>
                    </div>
                    <div class="ibox-content">
                        @include('backend.product.product.component.content', ['model' => ($product) ?? null])
                    </div>
                </div>
                @include('backend.product.product.component.noxh')
                @include('backend.dashboard.component.album', ['model' => ($product) ?? null])

                {{-- Khoi "San pham co nhieu phien ban" (mau sac, size...) la
                     cua ban hang, du an nha o khong co bien the nao ca. --}}
                {{-- @include('backend.product.product.component.variant') --}}
                {{-- @include('backend.product.product.component.program') --}}
                @include('backend.dashboard.component.seo', ['model' => ($product) ?? null])
            </div>
            <div class="col-lg-3">
                @include('backend.product.product.component.aside')
            </div>

            {{-- Cac cot ban hang khong hien ra nua nhung van phai gui len,
                 xem chu thich trong chinh file do. --}}
            @include('backend.product.product.component.o-an')
        </div>
        <div class="text-right mb15 fixed-bottom">
            <button class="btn btn-primary" type="submit" name="send" value="send_and_stay">{{ __('messages.save') }}</button>
            <button class="btn btn-success" type="submit" name="send" value="send_and_exit">Đóng</button>
        </div>
    </div>
</form>
