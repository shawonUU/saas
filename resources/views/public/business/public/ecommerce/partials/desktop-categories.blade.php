
<style>
.sf-category-rail .sf-rail-switch {
    width: 30px;
    height: 30px;
    min-width: 30px;
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #697176;
    appearance: none;
    -webkit-appearance: none;
    transform: none !important;
    transition: none !important;
}

.sf-category-rail .sf-rail-switch:hover,
.sf-category-rail .sf-rail-switch:active {
    background: transparent !important;
    box-shadow: none !important;
}

.sf-category-rail .sf-rail-switch:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 2px;
}

.sf-category-rail .sf-rail-switch::before,
.sf-category-rail .sf-rail-switch::after {
    content: none !important;
    display: none !important;
}

.sf-category-rail .sf-rail-switch svg,
.sf-category-rail .sf-rail-arrow svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    transform: none !important;
    animation: none !important;
    transition: none !important;
    pointer-events: none;
}

/* Closed = Down */
.sf-category-rail .sf-rail-switch[aria-expanded="false"] .sf-icon-down {
    display: block !important;
}
.sf-category-rail .sf-rail-switch[aria-expanded="false"] .sf-icon-up {
    display: none !important;
}

/* Opened = Up */
.sf-category-rail .sf-rail-switch[aria-expanded="true"] .sf-icon-down {
    display: none !important;
}
.sf-category-rail .sf-rail-switch[aria-expanded="true"] .sf-icon-up {
    display: block !important;
}

.sf-category-rail .sf-rail-arrow {
    width: 30px;
    height: 30px;
    min-width: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    color: #697176;
    transform: none !important;
}

.sf-category-rail .sf-rail-children[hidden] {
    display: none !important;
}
</style>

<aside class="sf-category-rail"
      data-category-rail
      aria-label="{{ t('Categories','ক্যাটাগরি') }}">

    <div class="sf-rail-list">

        @foreach(($categories ?? collect()) as $category)
            @php
                $kids = $category->children ?? collect();
                $active = (int)request('category') === (int)$category->id;
            @endphp

            <div class="sf-rail-group {{ $active ? 'is-active' : '' }}">

                <div class="sf-rail-row">

                    <a href="{{ route('business.customer.shop', [
                        'category' => $category->id
                    ]) }}" class="sf-rail-item">

                        <span class="sf-rail-emoji">
                            {{ ['♧','♙','◇','♢','✿','♥','♤','✧','♧','✦','◈','♢'][$loop->index % 12] }}
                        </span>

                        <span class="sf-rail-label">
                            {{ t($category->name,$category->name_bn ?? $category->name) }}
                        </span>
                    </a>

                    @if($kids->count())
                        <button
                            type="button"
                            class="sf-rail-switch"
                            data-rail-switch
                            aria-expanded="{{ $active ? 'true' : 'false' }}"
                            aria-label="{{ t('Toggle subcategories','সাবক্যাটাগরি খুলুন বা বন্ধ করুন') }}">

                            <!-- Down Arrow -->
                            <svg class="sf-icon-down"
                                 viewBox="0 0 24 24"
                                 aria-hidden="true">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>

                            <!-- Up Arrow -->
                            <svg class="sf-icon-up"
                                 viewBox="0 0 24 24"
                                 aria-hidden="true">
                                <path d="M6 15l6-6 6 6"/>
                            </svg>

                        </button>
                    @else
                        <span class="sf-rail-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M9 6l6 6-6 6"/>
                            </svg>
                        </span>
                    @endif

                </div>

                @if($kids->count())
                    <div class="sf-rail-children"
                         @if(!$active) hidden @endif>

                        @foreach($kids as $kid)
                            <a href="{{ route('business.customer.shop', [
                                'category' => $category->id,
                                'sub_category' => $kid->id
                            ]) }}"
                            @class([
                                'active' => (int)request('sub_category') === (int)$kid->id
                            ])>
                                {{ t($kid->name,$kid->name_bn ?? $kid->name) }}
                            </a>
                        @endforeach

                    </div>
                @endif

            </div>
        @endforeach

    </div>

    <div class="sf-rail-bottom">

        <a href="{{ route('business.customer.shop') }}">
            <strong>⌕</strong>
            <span>{{ t('All Products','সব পণ্য') }}</span>
        </a>

        <a href="{{ route('business.customer.wishlist') }}">
            <strong>♡</strong>
            <span>{{ t('Wishlist','উইশলিস্ট') }}</span>
        </a>

    </div>

</aside>


<script>
(function () {
    document.querySelectorAll('[data-category-rail]').forEach(function (rail) {
        const buttons = rail.querySelectorAll('[data-rail-switch]');

        buttons.forEach(function (button) {
            if (button.dataset.initialized === '1') return;
            button.dataset.initialized = '1';

            const group = button.closest('.sf-rail-group');
            const children = group?.querySelector('.sf-rail-children');
            if (!children) return;

            button.setAttribute('aria-expanded', String(!children.hidden));

            button.addEventListener('click', function () {
                const shouldOpen = children.hidden;

                // Close all categories
                buttons.forEach(function (otherButton) {
                    const otherGroup = otherButton.closest('.sf-rail-group');
                    const otherChildren = otherGroup?.querySelector('.sf-rail-children');

                    if (otherChildren) {
                        otherChildren.hidden = true;
                        otherButton.setAttribute('aria-expanded', 'false');
                    }
                });

                // Open selected category
                if (shouldOpen) {
                    children.hidden = false;
                    button.setAttribute('aria-expanded', 'true');
                }
            });
        });
    });
})();
</script>

