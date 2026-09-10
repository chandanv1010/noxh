@extends('frontend.homepage.layout')
@section('content')
    <div class="product-catalogue page-wrapper">
        <div class="uk-container uk-container-center mt40">
            <div class="panel-body">
                <h2 class="heading-1 mb20"><span>{{ $seo['meta_title'] }}</span></h2>
                @if(!is_null($products) && count($products))
                <div class="product-list mb30">
                    <div class="product-grid">
                        @foreach ($products as $keyPost => $valPost)
                            @php
                                $title = $valPost->languages->first()->pivot->name ?? $valPost->name ?? '';
                                $image = asset($valPost->image);
                                $href  = write_url($valPost->languages->first()->pivot->canonical ?? '#');
                                $price = number_format($valPost->price ?? 0, 0, ',', '.');
                                $oldPrice = number_format($valPost->combo_price ?? 0, 0, ',', '.');
                                $warrantyText = (isset($valPost->warranty) && $valPost->warranty > 0) ? 'Bảo hành ' . $valPost->warranty . ' tháng' : 'Bảo hành 12 tháng';
                            @endphp

                            <div class="product-card-wrapper">
                                <div class="product-card">
                                    <a href="{{ $href }}" class="product-card-image-link">
                                        <img src="{{ $image }}" alt="{{ $title }}" class="product-card-img">
                                    </a>
                                    
                                    <div class="product-card-info">
                                        <h3 class="product-card-title">
                                            <a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a>
                                        </h3>
                                        
                                        <div class="product-card-warranty">{{ $warrantyText }}</div>
                                        
                                        <div class="product-card-price-row uk-flex uk-flex-middle">
                                            @if(($valPost->combo_price ?? 0) > 0)
                                                <span class="price-old">{{ $oldPrice }}đ</span>
                                            @endif
                                            <span class="price-current">{{ $price }}đ</span>
                                        </div>
                                        
                                        <div class="product-card-actions uk-flex uk-flex-middle">
                                            <a href="{{ $href }}" class="btn-buy-now">MUA NGAY</a>
                                            <a href="{{ $href }}" class="btn-cart">
                                                <img src="{{ asset('vendor/frontend/img/project/icons/Group 9890.png') }}" alt="" class="cart-btn-icon">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="uk-text-center search-paginate">
                        @if ($products->hasPages())
                            <ul class="pagination">
                                {{-- Previous Page Link --}}
                                @php
                                    $prevPageUrl = ($products->currentPage() > 1) ? str_replace('?page=', '/trang-', $products->previousPageUrl()).config('apps.general.suffix') : null;
                                @endphp
                                @if ($prevPageUrl)
                                    <li class="page-item"><a class="page-link" href="{{ $prevPageUrl }}">Previous</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                @endif

                                {{-- Pagination Links --}}
                                @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                                    @php
                                        // $paginationUrl = str_replace('?page=', '/trang-', $url).config('apps.general.suffix');
                                        // $paginationUrl = ($page == 1) ? str_replace('/trang-'.$page, '', $paginationUrl) : $paginationUrl;
                                        $paginationUrl = $url;
                                    @endphp
                                    <li class="page-item {{ ($page == $products->currentPage()) ? 'active' : '' }}"><a class="page-link" href="{{ $paginationUrl }}">{{ $page }}</a></li>
                                @endforeach

                                {{-- Next Page Link --}}
                                @php
                                    $nextPageUrl = ($products->hasMorePages()) ? str_replace('?page=', '/trang-', $products->nextPageUrl()).config('apps.general.suffix') : null;
                                @endphp
                                @if ($nextPageUrl)
                                    <li class="page-item"><a class="page-link" href="{{ $nextPageUrl }}">Next</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                @endif
                            </ul>
                        @endif

                    </div>
                </div>
                @else
                    <div class="pt20 pb20">
                        Không có sản phẩm phù hợp....
                    </div>

                @endif
            </div>

        </div>
    </div>

@endsection

