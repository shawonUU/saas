<a class="ret-btn alt" href="{{ route('business.admin.retail.products.edit',['record'=>$record->id]) }}">{{ t('View / Edit','দেখুন / সম্পাদনা') }}</a>

@if($record->is_active)<form method="POST" action="{{ route('business.admin.retail.products.destroy',['record'=>$record->id]) }}" data-confirm="{{ t('Deactivate this product?','পণ্যটি নিষ্ক্রিয় করবেন?') }}" onsubmit="return confirm(this.dataset.confirm)" style="display:inline">@csrf @method('DELETE')<button class="ret-btn alt" type="submit">{{ t('Deactivate','নিষ্ক্রিয়') }}</button></form>@endif
