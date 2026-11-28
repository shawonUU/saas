@php
    $listuiCount = method_exists($paginator,'total') ? $paginator->total() : $paginator->count();
    $listuiLast = method_exists($paginator,'lastPage') ? $paginator->lastPage() : $paginator->currentPage();
@endphp
<div class="cfg-bottom listui-footer">
 <span>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} {{ t('of','মোট') }} {{ $listuiCount }} {{ t('records','টি রেকর্ড') }}</span>
 <nav class="cfg-pagination" aria-label="{{ t('Pagination','পেজিনেশন') }}">
  @if($paginator->previousPageUrl())<a href="{{ $paginator->previousPageUrl() }}">{{ t('Prev','আগের') }}</a>@else<span class="is-disabled">{{ t('Prev','আগের') }}</span>@endif
  <span>{{ $paginator->currentPage() }} / {{ max(1, $listuiLast) }}</span>
  @if($paginator->nextPageUrl())<a href="{{ $paginator->nextPageUrl() }}">{{ t('Next','পরের') }}</a>@else<span class="is-disabled">{{ t('Next','পরের') }}</span>@endif
 </nav>
</div>
