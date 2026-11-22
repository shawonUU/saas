@if(!$record->settled_at)
<form method="POST" action="{{ route('business.admin.retail.gateway_settlements.settle',$record->id) }}">@csrf
<input class="ret-input" style="width:100px" name="fee" type="number" step=".01" min="0" max="{{ $record->amount }}" value="0" aria-label="Gateway Fee" required>
<input class="ret-input" style="width:130px" name="reference" placeholder="Bank reference" required><button class="ret-btn" type="submit">{{ t('Settle','মিল করুন') }}</button></form>
@endif
