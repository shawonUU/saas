<aside class="sf-category-rail" aria-label="{{ t('Categories','ক্যাটাগরি') }}">
 <div class="sf-rail-list">
 @foreach(($categories??collect()) as $category)
  @php $kids=$category->children??collect(); $active=(int)request('category')===(int)$category->id; @endphp
  <div class="sf-rail-group {{ $active?'is-active':'' }}">
  <div class="sf-rail-row"><a href="{{ route('business.customer.shop',['category'=>$category->id]) }}" class="sf-rail-item"><span class="sf-rail-emoji">{{ ['♧','♙','◇','♢','✿','♥','♤','✧','♧','✦','◈','♢'][$loop->index%12] }}</span><span class="sf-rail-label">{{ $category->name }}</span></a>
  @if($kids->count())<button class="sf-rail-expand" aria-expanded="{{ $active?'true':'false' }}" type="button" data-category-toggle title="{{ t('Show subcategories','সাবক্যাটাগরি দেখুন') }}">⌄</button>@else<span class="sf-rail-arrow">›</span>@endif</div>
  @if($kids->count())<div class="sf-rail-children" @if(!$active) hidden @endif>@foreach($kids as $kid)<a href="{{ route('business.customer.shop',['category'=>$category->id,'sub_category'=>$kid->id]) }}" @class(['active'=>(int)request('sub_category')===(int)$kid->id])>{{ $kid->name }}</a>@endforeach</div>@endif
  </div>
 @endforeach
 </div>
 <div class="sf-rail-bottom"><a href="{{ route('business.customer.shop') }}"><strong>⌕</strong><span>{{ t('All Products','সব পণ্য') }}</span></a><a href="{{ route('business.customer.wishlist') }}"><strong>♡</strong><span>{{ t('Wishlist','উইশলিস্ট') }}</span></a></div>
</aside>
