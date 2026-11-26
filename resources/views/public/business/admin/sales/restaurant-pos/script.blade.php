<script>
(() => {
    'use strict';
    const root = document.getElementById('restaurant-pos');
    if (!root) return;
    const $ = id => document.getElementById(id), form = $('rpos-form'), field = name => form.elements.namedItem(name), paymentForm = $('rpos-payment-form'), payField = name => paymentForm.elements.namedItem(name);
    const catalog = {{ \Illuminate\Support\Js::from($catalog) }}, routes = {{ \Illuminate\Support\Js::from($routes) }}, permissions = {{ \Illuminate\Support\Js::from($permissions) }}, initialOrder = {{ \Illuminate\Support\Js::from($selectedOrder ?? null) }}, initialOpen = {{ \Illuminate\Support\Js::from($openOrders) }}, modifiers = {{ \Illuminate\Support\Js::from($modifiers->map(fn($modifier)=>['id'=>$modifier->id,'product_id'=>$modifier->product_id,'name'=>t($modifier->name,$modifier->name_bn),'price'=>(float)$modifier->price])->values()) }};
    const reservation = {{ \Illuminate\Support\Js::from(isset($reservation) && $reservation ? ['id'=>$reservation->id,'dining_table_id'=>$reservation->dining_table_id,'guest_count'=>$reservation->guest_count,'customer_name'=>$reservation->customer_name,'customer_phone'=>$reservation->customer_phone] : null) }}, initialTable = {{ \Illuminate\Support\Js::from((int)request('table',0)) }};
    const locale = {{ \Illuminate\Support\Js::from(session('portal_lang','en')) }}, storageKey = {{ \Illuminate\Support\Js::from('restaurant-pos-'.$business->id.'-'.$user->id) }};
    const initialFloor = {{ \Illuminate\Support\Js::from($tableState ?? ['tables'=>[]]) }};
    const posSettings = {{ \Illuminate\Support\Js::from($posSettings ?? ['kitchen_mode'=>'optional','auto_send_to_kitchen'=>false,'default_service'=>'takeaway']) }}, popularIds = {{ \Illuminate\Support\Js::from($popularVariantIds ?? []) }};
    const defaultService = ['takeaway','dine_in','delivery'].includes(posSettings.default_service)?posSettings.default_service:'takeaway', popularRank = new Map(popularIds.map((id,index)=>[String(id),index]));
    const words = {{ \Illuminate\Support\Js::from([
        'new'=>t('New order','নতুন অর্ডার'),'draft'=>t('Draft','খসড়া'),'confirmed'=>t('Billed','বিল হয়েছে'),'cancelled'=>t('Cancelled','বাতিল'),'queued'=>t('Queued','অপেক্ষমাণ'),'preparing'=>t('Preparing','প্রস্তুত হচ্ছে'),'ready'=>t('Ready','প্রস্তুত'),'served'=>t('Served','পরিবেশিত'),'void'=>t('Voided','বাতিল'),
        'noFoods'=>t('No matching food found.','কোনো খাবার পাওয়া যায়নি।'),'empty'=>t('Select food to start this order.','অর্ডার শুরু করতে খাবার নির্বাচন করুন।'),'foods'=>t('items','টি খাবার'),'remove'=>t('Remove','সরান'),'voidItem'=>t('Void item','আইটেম বাতিল'),'notes'=>t('Kitchen note','কিচেন নোট'),'options'=>t('Notes & modifiers','নোট ও অতিরিক্ত উপাদান'),'quantity'=>t('Quantity','পরিমাণ'),'unavailable'=>t('Currently unavailable','বর্তমানে পাওয়া যাচ্ছে না'),
        'noPermission'=>t('You do not have permission for this action.','এই কাজের অনুমতি আপনার নেই।'),'invalid'=>t('Check the order details.','অর্ডারের তথ্য যাচাই করুন।'),'table'=>t('Select a table for dine-in.','ডাইন-ইনের জন্য টেবিল নির্বাচন করুন।'),'noItems'=>t('Add at least one food item.','কমপক্ষে একটি খাবার যোগ করুন।'),'saveFirst'=>t('Save the order first.','প্রথমে অর্ডার সংরক্ষণ করুন।'),'saving'=>t('Saving…','সংরক্ষণ হচ্ছে…'),'saved'=>t('Saved','সংরক্ষিত'),'unsaved'=>t('Unsaved changes · stored on this device','অসংরক্ষিত পরিবর্তন · এই ডিভাইসে রাখা আছে'),'restored'=>t('Your unsent draft has been restored.','আপনার অসম্পূর্ণ খসড়া ফেরত আনা হয়েছে।'),
        'connection'=>t('The result could not be confirmed. Retry the same request before editing or starting another order.','ফলাফল নিশ্চিত করা যায়নি। পরিবর্তন বা নতুন অর্ডারের আগে একই অনুরোধ আবার চেষ্টা করুন।'),'retry'=>t('Retry pending request','অপেক্ষমাণ অনুরোধ আবার চেষ্টা করুন'),'stale'=>t('This order changed elsewhere. Your unsaved work is kept on this device. Load the latest order before continuing.','অন্য জায়গা থেকে অর্ডার পরিবর্তন হয়েছে। আপনার অসম্পূর্ণ কাজ এই ডিভাইসে আছে। চালিয়ে যাওয়ার আগে সর্বশেষ অর্ডার দেখুন।'),'failed'=>t('Unable to complete the action.','কাজটি সম্পন্ন করা যায়নি।'),'discard'=>t('Leave the unsaved changes and open another order?','অসংরক্ষিত পরিবর্তন রেখে অন্য অর্ডার খুলবেন?'),'reload'=>t('Load the latest order? The previous draft will remain saved as a recovery copy on this device.','সর্বশেষ অর্ডার দেখবেন? আগের খসড়া এই ডিভাইসে পুনরুদ্ধারের কপি হিসেবে থাকবে।'),
        'sent'=>t('Order sent to the kitchen.','অর্ডার কিচেনে পাঠানো হয়েছে।'),'cancelReason'=>t('Reason for cancelling this order:','অর্ডার বাতিলের কারণ:'),'voidReason'=>t('Reason for voiding this item:','আইটেম বাতিলের কারণ:'),'closeConfirm'=>t('Close this table and finish service?','টেবিল বন্ধ করে সার্ভিস শেষ করবেন?'),'serveConfirm'=>t('Mark all ready items as served?','সব প্রস্তুত আইটেম পরিবেশিত হিসেবে চিহ্নিত করবেন?'),
        'total'=>t('Total','মোট'),'estimate'=>t('Estimated total','আনুমানিক মোট'),'receive'=>t('Receive payment','পেমেন্ট গ্রহণ'),'bill'=>t('Bill & payment','বিল ও পেমেন্ট'),'paid'=>t('This order is fully paid.','এই অর্ডারের সম্পূর্ণ টাকা পরিশোধ হয়েছে।'),'amount'=>t('Check the received amount.','গৃহীত টাকা যাচাই করুন।'),'customer'=>t('Select a registered customer to keep an outstanding balance.','বাকি রাখার জন্য নিবন্ধিত কাস্টমার নির্বাচন করুন।'),'paymentConfirm'=>t('Confirm payment','পেমেন্ট নিশ্চিত করুন'),'processing'=>t('Processing…','প্রক্রিয়াধীন…'),'success'=>t('Saved successfully.','সফলভাবে সংরক্ষিত হয়েছে।'),'limit'=>t('The item or quantity limit has been reached.','আইটেম বা পরিমাণের সীমা পূর্ণ হয়েছে।'),'storage'=>t('This browser cannot preserve the draft. Keep this page open until the order is saved.','এই ব্রাউজারে খসড়া রাখা যাচ্ছে না। অর্ডার সংরক্ষণ না হওয়া পর্যন্ত পেজ খোলা রাখুন।'),'closed'=>t('Closed','বন্ধ'),'occupied'=>t('Occupied','ব্যবহৃত'),'dine_in'=>t('Dine-in','ডাইন-ইন'),'takeaway'=>t('Counter / Takeaway','কাউন্টার / টেকঅ্যাওয়ে'),'delivery'=>t('Delivery','ডেলিভারি'),
        'available'=>t('Available','খালি'),'reserved'=>t('Reserved','সংরক্ষিত'),'partially_ready'=>t('Partially ready','আংশিক প্রস্তুত'),'partially_served'=>t('Partially served','আংশিক পরিবেশিত'),'seats'=>t('seats','আসন'),'guests'=>t('guests','অতিথি'),'selected'=>t('Selected','নির্বাচিত'),'chooseTable'=>t('Choose a table','টেবিল বেছে নিন'),'startTable'=>t('Start an order','অর্ডার শুরু করুন'),'openTable'=>t('Open current order','চলমান অর্ডার খুলুন'),'seatReservation'=>t('Seat reservation','বুকিং থেকে বসান'),'noTables'=>t('No matching tables.','মিলছে এমন টেবিল নেই।'),'noTablesConfigured'=>t('No active tables are available.','কোনো সক্রিয় টেবিল নেই।'),'updatingFloor'=>t('Updating tables…','টেবিল হালনাগাদ হচ্ছে…'),'floorUpdated'=>t('Updated','হালনাগাদ'),'floorOffline'=>t('Could not refresh · showing last update','হালনাগাদ হয়নি · আগের অবস্থা দেখানো হচ্ছে'),'emptyTitle'=>t('Your order starts here','এখান থেকে অর্ডার শুরু করুন'),'addItem'=>t('Add to order','অর্ডারে যোগ করুন'),'inOrder'=>t('in order','অর্ডারে আছে'),'until'=>t('Until','পর্যন্ত'),'paymentChanged'=>t('This order changed elsewhere. Close this window and load the latest order before taking payment.','অন্য জায়গা থেকে অর্ডার পরিবর্তন হয়েছে। এই উইন্ডো বন্ধ করে সর্বশেষ অর্ডার দেখার পর পেমেন্ট নিন।'),
        'pay'=>t('Pay','পেমেন্ট'),'payNew'=>t('Pay & new order','পেমেন্ট ও নতুন অর্ডার'),'closeTable'=>t('Close table','টেবিল বন্ধ করুন'),'balanceWarning'=>t('Outstanding balance:','অবশিষ্ট বাকি:'),'unpaid'=>t('Unpaid','অপরিশোধিত'),'partial'=>t('Part paid','আংশিক পরিশোধিত'),'fullyPaid'=>t('Paid','পরিশোধিত'),'walkIn'=>t('Walk-in','সাধারণ অতিথি'),'notSent'=>t('Not sent','পাঠানো হয়নি'),
        'kitchen_optional'=>t('Kitchen optional','কিচেন ঐচ্ছিক'),'kitchen_off'=>t('Kitchen off','কিচেন বন্ধ'),'kitchen_required'=>t('Kitchen tracking required','কিচেনের ধাপ অনুসরণ আবশ্যক'),'optionalHint'=>t('Take payment without kitchen updates. Print a KOT whenever needed.','কিচেনের অবস্থা বদলানো ছাড়াই পেমেন্ট নিন। প্রয়োজন হলে কিচেন টিকিট প্রিন্ট করুন।'),'requiredHint'=>t('Payment can be taken now. Serve all items before closing a dine-in table.','এখনই পেমেন্ট নেওয়া যাবে। ডাইন-ইন টেবিল বন্ধের আগে সব খাবার পরিবেশিত হিসেবে চিহ্নিত করুন।'),
        'paymentReceived'=>t('Payment received','পেমেন্ট পাওয়া গেছে'),'readyForNext'=>t('Ready for the next order','পরের অর্ডারের জন্য প্রস্তুত'),'tableStillOccupied'=>t('Table stays occupied until you close it','বন্ধ না করা পর্যন্ত টেবিল ব্যবহৃত থাকবে'),'change'=>t('Change','ফেরত টাকা'),'cart'=>t('Cart','কার্ট'),'ticketReady'=>t('Order saved. Use Print KOT to open the ticket.','অর্ডার সংরক্ষিত হয়েছে। টিকিট খুলতে কিচেন টিকিট প্রিন্ট লিংকে চাপ দিন।'),
        'unitPrice'=>t('Unit price','দর'),'optionsShort'=>t('Options','বিকল্প'),'tablesLabel'=>t('Tables','টেবিল'),'printBill'=>t('Bill preview','বিল প্রিভিউ'),'printKot'=>t('Kitchen ticket','কিচেন টিকিট')
    ]) }};
    const products = new Map(catalog.map(product => [String(product.id),product])), openOrders = new Map(Object.values(initialOpen || {}).map(order => [String(order.id),order]));
    const tables = new Map((initialFloor.tables || []).map(table => [String(table.id),table]));
    const releasedOrderIds = new Set();
    const uuid = () => window.crypto?.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => { const number=Math.floor(Math.random()*16); return (character==='x'?number:(number&3|8)).toString(16); });
    const money = amount => '৳' + Number(amount || 0).toLocaleString(locale==='bn'?'bn-BD':'en-BD',{minimumFractionDigits:2,maximumFractionDigits:2}), round = value => Math.round((value+Number.EPSILON)*100)/100;
    let order = null, lines = [], requestKey = uuid(), checkoutKey = uuid(), reservationId = reservation?.id || null, dirty = false, busy = false, pending = null, category = popularRank.size?'popular':'', activePayment = false, stale = false, lastError = '', lastSale = null;
    let browsingTables = false, selectedReservationTableId = reservation?.dining_table_id || null;
    let tableArea = '', floorBusy = false, floorRefreshQueued = false, floorEpoch = 0, floorFailed = false, floorUpdatedAt = initialFloor.updated_at || null;
    const number = value => Number(value || 0).toLocaleString(locale==='bn'?'bn-BD':'en-BD',{maximumFractionDigits:3});
    const tableName = table => locale==='bn'?(table.name_bn || table.name):table.name;
    const areaName = table => locale==='bn'?(table.area_name_bn || table.area_name):table.area_name;
    const route = (action,item) => routes[action].replace('__ORDER__',String(order?.id || '')).replace('__ITEM__',String(item || ''));
    const element = (tag,className,text) => { const node=document.createElement(tag); if(className)node.className=className; if(text!==undefined)node.textContent=String(text); return node; };
    function decorativeIcon(name,className=''){
        const namespace='http://www.w3.org/2000/svg',svg=document.createElementNS(namespace,'svg');
        const plate=name==='plate';
        for(const [key,value] of Object.entries({viewBox:plate?'0 0 32 32':'0 0 24 24',width:plate?'44':'28',height:plate?'44':'28',fill:'none',stroke:'currentColor','stroke-width':'1.5','stroke-linecap':'round','stroke-linejoin':'round','aria-hidden':'true'}))svg.setAttribute(key,value);
        if(className)svg.setAttribute('class',className);
        if(plate){const circle=document.createElementNS(namespace,'circle');circle.setAttribute('cx','16');circle.setAttribute('cy','16');circle.setAttribute('r','8');svg.append(circle);}
        const path=document.createElementNS(namespace,'path');path.setAttribute('d',plate?'M3 5v6a2 2 0 0 0 4 0V5M5 5v22M3 9h4M29 5v22M29 5c-5 3-5 10 0 10':'M6 3h12v18l-3-2-3 2-3-2-3 2V3ZM9 7h6M9 11h6M9 15h3');svg.append(path);return svg;
    }
    const button = (text,className,handler) => { const node=element('button',className,text); node.type='button'; node.addEventListener('click',handler); return node; };
    const alert = (message,error=false) => { $('rpos-alert').textContent=message || ''; $('rpos-alert').hidden=!message; $('rpos-alert').className='rpos-alert '+(error?'error':'success'); };
    const fields = ['service_type','dining_table_id','guest_count','customer_id','customer_name','customer_phone','customer_address','restaurant_notes','discount','coupon_code','tax_id','shipping'];
    const canEdit = () => !order || order.status==='draft';
    const workflow = () => order?.workflow || posSettings;
    const paymentState = candidate => candidate.status==='confirmed' && Number(candidate.due)<=.009?'fullyPaid':Number(candidate.paid || 0)+Number(candidate.credited || 0)>.009?'partial':'unpaid';
    const payButtonText = () => field('service_type').value==='takeaway'?words.payNew:order?.status==='confirmed'?words.receive:words.pay;
    const snapshot = () => {
        const data={request_key:requestKey,version:order?.version,reservation_id:reservationId};
        for(const name of fields)data[name]=field(name).value;
        if(data.service_type!=='dine_in')data.dining_table_id=null;
        if(data.service_type!=='delivery')data.shipping=0;
        data.items=lines.map(line=>({...(line.id?{id:line.id}:{}),variant_id:line.variant_id,quantity:line.quantity,kitchen_note:line.kitchen_note || '',modifier_ids:line.modifier_ids || []}));
        return data;
    };
    function persist(){
        try{localStorage.setItem(storageKey,JSON.stringify({orderId:order?.id || null,baseVersion:order?.version || null,requestKey,reservationId,browsingTables,data:snapshot(),dirty,pending,savedAt:Date.now()}));}
        catch(_){if(dirty)alert(words.storage,true);}
    }
    function rememberRecovery(){try{const draft=localStorage.getItem(storageKey);if(draft)localStorage.setItem(storageKey+'-recovery',draft);}catch(_) {}}
    function markDirty(){dirty=true;stale=false;persist();renderTotals();updateControls();}
    function renderLastSale(){
        const visible=lastSale && Number.isSafeInteger(Number(lastSale.id)) && Number(lastSale.id)>0 && Number(order?.id || 0)!==Number(lastSale.id);
        $('rpos-last-sale').hidden=!visible;if(!visible)return;
        $('rpos-last-sale-title').textContent=words.paymentReceived+' · '+lastSale.order_no;
        $('rpos-last-sale-detail').textContent=money(lastSale.total)+(Number(lastSale.change)>0?' · '+words.change+' '+money(lastSale.change):'');
        $('rpos-last-receipt').href=routes.receipt.replace('__ORDER__',String(Number(lastSale.id)));
        $('rpos-last-kot').href=routes.kot.replace('__ORDER__',String(Number(lastSale.id)));
    }
    function finishPayment(candidate,payload){
        activePayment=false;$('rpos-payment').close();
        if(candidate.status==='confirmed' && Number(candidate.due)<=.009){
            lastSale={id:candidate.id,order_no:candidate.order_no,total:candidate.total,change:candidate.change};
            try{localStorage.setItem(storageKey+'-last-sale',JSON.stringify(lastSale));}catch(_){}
            renderLastSale();
            if(candidate.service_type==='takeaway' && payload.payment_mode==='full' && beginNewOrder(null,'takeaway')){
                alert('');if(window.matchMedia('(pointer: fine)').matches)$('rpos-search').focus({preventScroll:true});
            }else if(candidate.service_type==='dine_in' && !candidate.closed_at && candidate.workflow?.table_release==='manual')alert(words.paid+' '+words.tableStillOccupied);
        }
        updateControls();
    }
    function kitchenPhase(candidate){
        const items=(candidate?.items || []).filter(item=>item.kitchen_status!=='void');
        if(!items.some(item=>['queued','preparing','ready','served'].includes(item.kitchen_status)))return null;
        if(items.every(item=>item.kitchen_status==='served'))return 'served';
        const hasReady=items.some(item=>item.kitchen_status==='ready');
        if(hasReady)return items.every(item=>['ready','served'].includes(item.kitchen_status))?'ready':'partially_ready';
        if(items.some(item=>item.kitchen_status==='preparing'))return 'preparing';
        if(items.some(item=>item.kitchen_status==='queued'))return 'queued';
        return 'partially_served';
    }
    function tableStatus(table){
        return table.occupied_order_id?'occupied':table.reservation?'reserved':'available';
    }
    function syncTableOrder(candidate){
        if(!candidate)return;
        const live=candidate.service_type==='dine_in' && !candidate.closed_at && ['draft','confirmed'].includes(candidate.status);
        for(const table of tables.values()){
            if(Number(table.occupied_order_id)===Number(candidate.id)){
                table.occupied_order_id=null;table.occupied_order_no=null;table.order=null;
                if(candidate.closed_at && Number(table.reservation?.id)===Number(candidate.reservation_id))table.reservation=null;
            }
        }
        if(live){
            const table=tables.get(String(candidate.dining_table_id));
            if(table){table.occupied_order_id=candidate.id;table.occupied_order_no=candidate.order_no;table.order=candidate;}
        }
    }
    function renderFloorSync(){
        const label=$('rpos-floor-sync');
        if(label){
            label.dataset.state=floorFailed?'error':floorBusy?'loading':'current';
            let stamp='';
            if(floorUpdatedAt){const date=new Date(floorUpdatedAt);if(!Number.isNaN(date.getTime()))stamp=date.toLocaleTimeString(locale==='bn'?'bn-BD':'en-BD',{hour:'2-digit',minute:'2-digit'});}
            label.textContent=floorBusy?words.updatingFloor:floorFailed?words.floorOffline:stamp?words.floorUpdated+' '+stamp:words.floorUpdated;
        }
        if($('rpos-refresh-tables'))$('rpos-refresh-tables').disabled=floorBusy || busy || !!pending;
    }
    function renderTables(){
        const holder=$('rpos-tables');if(!holder)return;
        const focused=holder.contains(document.activeElement)?document.activeElement?.closest('[data-table-id]')?.dataset.tableId:null;
        const search=($('rpos-table-search')?.value || '').trim().toLowerCase();
        const all=[...tables.values()],counts={available:0,occupied:0,reserved:0};
        for(const table of all)counts[tableStatus(table)]++;
        if($('rpos-table-summary'))$('rpos-table-summary').textContent=['available','occupied','reserved'].map(state=>number(counts[state])+' '+words[state]).join(' · ');
        const visible=all.filter(table=>(!tableArea || String(table.area_id)===tableArea) && [tableName(table),areaName(table),table.occupied_order_no,table.reservation?.customer_name].join(' ').toLowerCase().includes(search));
        holder.replaceChildren();
        if(!visible.length)holder.append(element('p','rst-empty',all.length?words.noTables:words.noTablesConfigured));
        for(const table of visible){
            const state=tableStatus(table),current=table.order,phase=current?kitchenPhase(current):null,payment=current?paymentState(current):null;
            const selected=field('service_type').value==='dine_in' && String(field('dining_table_id').value)===String(table.id);
            const card=button('','rpos-table-card'+(selected?' is-selected':''),()=>selectTable(table.id));
            card.dataset.tableId=table.id;card.dataset.state=state;card.dataset.phase=state==='occupied' && phase?phase:'';card.dataset.payment=payment || '';
            card.setAttribute('aria-pressed',String(selected));
            card.disabled=busy || !!pending || stale || (!table.occupied_order_id && !table.reservation?.order_id && !permissions.create);
            card.setAttribute('aria-label',[tableName(table),words[state],payment?words[payment]:'',phase?words[phase]:'',selected?words.selected:''].filter(Boolean).join(' · '));
            card.title=[tableName(table),areaName(table),state==='occupied'?words.openTable:state==='reserved'?words.seatReservation:words.startTable].filter(Boolean).join(' · ');
            const top=element('span','rpos-table-top');
            top.append(element('strong','rpos-table-name',tableName(table)),element('span','rpos-table-state',words[state]));
            const seats=element('span','rpos-table-seats',number(table.capacity)+' '+words.seats+(current?' · '+number(current.guest_count)+' '+words.guests:''));
            const meta=element('span','rpos-table-meta',current?.order_no || table.occupied_order_no || areaName(table) || '');
            const bottom=element('span','rpos-table-bottom');
            if(current){
                const paymentBadge=element('span','rpos-table-payment',words[payment]);paymentBadge.dataset.payment=payment;
                bottom.append(paymentBadge,element('strong','rpos-table-amount',money(current.total)));
                if(phase){const phaseBadge=element('span','rpos-table-phase',words[phase]);phaseBadge.dataset.phase=phase;meta.append(document.createTextNode(' · '),phaseBadge);}
            }else if(state==='occupied')bottom.append(element('span','rpos-table-phase',words.openTable));
            else if(table.reservation){
                const reserved=table.reservation;meta.textContent=reserved.customer_name || words.reserved;
                const date=new Date(reserved.ends_at),time=Number.isNaN(date.getTime())?'':date.toLocaleTimeString(locale==='bn'?'bn-BD':'en-BD',{hour:'2-digit',minute:'2-digit'});
                bottom.append(element('span','rpos-table-phase',number(reserved.guest_count)+' '+words.guests),element('span','rpos-table-time',time?(locale==='bn'?time+' '+words.until:words.until+' '+time):''));
            }else bottom.append(element('span','rpos-table-phase',selected?words.selected:words.startTable));
            card.append(top,seats,meta,bottom);
            if(selected){const check=element('span','rpos-table-selected','✓');check.setAttribute('aria-hidden','true');card.append(check);}
            holder.append(card);
        }
        const selected=tables.get(String(field('dining_table_id').value));
        if($('rpos-selected-table'))$('rpos-selected-table').textContent=selected?[tableName(selected),areaName(selected)].filter(Boolean).join(' · '):words.chooseTable;
        const guestLimit=field('service_type').value==='dine_in' && selected?.capacity?Math.min(1000,Number(selected.capacity)):1000;
        field('guest_count').max=String(guestLimit);
        if(focused)holder.querySelector('[data-table-id="'+String(Number(focused))+'"]')?.focus({preventScroll:true});
        renderFloorSync();
    }
    function beginNewOrder(tableId=null,preferredService=null){
        if(busy || pending || stale || !permissions.create || (dirty && !confirm(words.discard)))return false;
        const service=tableId?'dine_in':preferredService || field('service_type').value || defaultService;
        $('rpos-search').value='';
        rememberRecovery();floorEpoch++;requestKey=uuid();checkoutKey=uuid();reservationId=null;selectedReservationTableId=null;browsingTables=false;setOrder(null);
        field('service_type').value=service;
        if(tableId){field('dining_table_id').value=String(tableId);markDirty();}
        serviceFields();renderTotals();updateControls();
        history.replaceState(null,'',routes.index+(tableId?'?table='+encodeURIComponent(tableId):''));persist();alert('');return true;
    }
    function focusProducts(){
        if(window.matchMedia('(pointer: fine)').matches)$('rpos-search').focus({preventScroll:true});
        if(window.matchMedia('(max-width:960px)').matches)window.scrollTo({top:0,behavior:'auto'});
    }
    function selectTable(id){
        if(busy || pending || stale || activePayment)return;
        const table=tables.get(String(id));if(!table)return;
        if(table.occupied_order_id){
            if(Number(table.occupied_order_id)===Number(order?.id)){browsingTables=false;serviceFields();persist();focusProducts();return;}
            loadOrder(table.occupied_order_id);return;
        }
        if(table.reservation?.order_id){loadOrder(table.reservation.order_id);return;}
        if(!permissions.create){alert(words.noPermission,true);return;}
        if(table.reservation && Number(table.reservation.id)!==Number(reservationId)){
            if(dirty && !confirm(words.discard))return;
            const selected=table.reservation;
            rememberRecovery();floorEpoch++;requestKey=uuid();checkoutKey=uuid();reservationId=selected.id;selectedReservationTableId=table.id;browsingTables=false;setOrder(null);
            field('service_type').value='dine_in';field('dining_table_id').value=String(table.id);field('guest_count').value=selected.guest_count || 1;
            field('customer_name').value=selected.customer_name || '';field('customer_phone').value=selected.customer_phone || '';
            serviceFields();markDirty();history.replaceState(null,'',routes.index+'?reservation='+encodeURIComponent(reservationId));focusProducts();return;
        }
        if(order){if(beginNewOrder(table.id))focusProducts();return;}
        if(reservationId && Number(selectedReservationTableId)!==Number(table.id)){
            if(dirty && !confirm(words.discard))return;
            rememberRecovery();reservationId=null;selectedReservationTableId=null;
        }
        browsingTables=false;field('service_type').value='dine_in';field('dining_table_id').value=String(table.id);serviceFields();markDirty();
        history.replaceState(null,'',routes.index+(reservationId?'?reservation='+encodeURIComponent(reservationId):'?table='+encodeURIComponent(table.id)));focusProducts();
    }
    async function reconcileReleasedOrders(epoch,signal){
        const refreshed=new Map();
        const results=await Promise.all([...releasedOrderIds].slice(0,8).map(async id=>{
            try{
                const response=await fetch(routes.show.replace('__ORDER__',id),{headers:{Accept:'application/json'},credentials:'same-origin',cache:'no-store',signal});
                if([403,404].includes(response.status))return {id,removed:true};
                const body=await response.json();if(response.ok && body.order)return {id,order:body.order};
            }catch(_){}
            return null;
        }));
        if(epoch!==floorEpoch || busy || pending)return refreshed;
        for(const result of results){
            if(!result)continue;
            releasedOrderIds.delete(result.id);
            if(result.removed)openOrders.delete(result.id);
            else{openOrders.set(result.id,result.order);refreshed.set(result.id,result.order);}
        }
        return refreshed;
    }
    async function refreshTables(){
        if(!routes.tables || !root.isConnected || document.hidden || busy || pending)return;
        if(floorBusy){floorRefreshQueued=true;return;}
        floorBusy=true;renderFloorSync();const epoch=floorEpoch,controller=new AbortController(),timeout=setTimeout(()=>controller.abort(),12000);
        try{
            const response=await fetch(routes.tables,{headers:{Accept:'application/json'},credentials:'same-origin',cache:'no-store',signal:controller.signal}),body=await response.json();
            if(!response.ok || body.success!==true || !Array.isArray(body.tables))throw Error(words.failed);
            if(epoch!==floorEpoch || busy || pending){floorRefreshQueued=true;return;}
            const liveIds=new Set(body.tables.map(table=>String(table.occupied_order_id || '')).filter(Boolean));
            for(const table of tables.values())if(table.occupied_order_id && !liveIds.has(String(table.occupied_order_id)) && openOrders.has(String(table.occupied_order_id)))releasedOrderIds.add(String(table.occupied_order_id));
            for(const id of liveIds)releasedOrderIds.delete(id);
            tables.clear();for(const table of body.tables){tables.set(String(table.id),table);if(table.order)openOrders.set(String(table.order.id),table.order);}
            const released=await reconcileReleasedOrders(epoch,controller.signal);
            if(epoch!==floorEpoch || busy || pending){floorRefreshQueued=true;return;}
            floorFailed=false;floorUpdatedAt=body.updated_at || new Date().toISOString();renderOpen();
            const selectedOrder=order && ([...tables.values()].map(table=>table.order).find(candidate=>Number(candidate?.id)===Number(order.id)) || released.get(String(order.id)));
            const missingOrder=order && !order.closed_at && order.status!=='cancelled' && order.service_type==='dine_in' && !selectedOrder;
            if(order && ((selectedOrder && Number(selectedOrder.version)!==Number(order.version)) || missingOrder)){
                if(dirty){if(!stale){rememberRecovery();stale=true;persist();alert(words.stale,true);updateControls();}}
                else if(activePayment){stale=true;$('rpos-payment-error').textContent=words.paymentChanged;updateControls();}
                else{
                    if(selectedOrder){setOrder(selectedOrder);persist();}
                    else void loadOrder(order.id,true,false);
                }
            }
            renderTables();
        }catch(_){floorFailed=true;}
        finally{
            clearTimeout(timeout);floorBusy=false;renderFloorSync();
            if(floorRefreshQueued){floorRefreshQueued=false;void refreshTables();}
        }
    }
    function syncStep(){
        const dineIn=field('service_type').value==='dine_in',selected=!!field('dining_table_id').value,tableStep=dineIn && (browsingTables || !selected);
        root.dataset.step=tableStep?'tables':'products';
        $('rpos-floor').hidden=!tableStep;$('rpos-catalog').hidden=tableStep;$('rpos-order-panel').hidden=tableStep;$('rpos-mobile-bar').hidden=tableStep;
        $('rpos-table-field').hidden=!dineIn || tableStep;$('rpos-continue-order').hidden=!tableStep || !selected;
        $('rpos-change-table').disabled=$('rpos-continue-order').disabled=busy || !!pending || stale || activePayment;
    }
    function serviceFields(){
        const type=field('service_type').value;
        root.dataset.service=type;$('rpos-order-context').hidden=type!=='dine_in';
        $('rpos-address-field').hidden=type!=='delivery';$('rpos-shipping-field').hidden=type!=='delivery';
        for(const tab of $('rpos-service').children){tab.classList.toggle('active',tab.dataset.service===type);tab.setAttribute('aria-pressed',String(tab.dataset.service===type));}
        syncStep();renderTables();
    }
    function setOrder(next,preserve=false){
        if(Number(next?.id || 0)!==Number(order?.id || 0) || !next)browsingTables=false;
        order=next || null; if(order?.reservation_id){reservationId=order.reservation_id;selectedReservationTableId=order.dining_table_id;} lines=(order?.items || []).map(line=>({...line,modifier_ids:(line.modifier_ids || []).map(Number)}));
        if(!preserve){for(const name of fields)field(name).value=order?.[name] ?? (name==='service_type'?defaultService:name==='guest_count'?'1':['discount','shipping'].includes(name)?'0':'');dirty=false;stale=false;}
        if(order){openOrders.set(String(order.id),order);syncTableOrder(order);$('rpos-order-title').textContent=order.order_no;}
        else $('rpos-order-title').textContent=words.new;
        $('rpos-status').textContent=words[order?.status || 'draft'] || order?.status;
        $('rpos-status').dataset.status=order?.status || 'draft';$('rpos-status').hidden=!order;$('rpos-closed').hidden=!order?.closed_at || order.service_type!=='dine_in';
        if(!order){$('rpos-guest-details').open=false;root.querySelector('.rpos-adjustments-details').open=false;}
        if(order?.service_type==='delivery' && $('rpos-guest-details'))$('rpos-guest-details').open=true;
        serviceFields();renderOpen();renderLines();renderTotals();renderLastSale();updateControls();
    }
    function renderOpen(){
        const select=$('rpos-open'),selected=select.value;select.replaceChildren(element('option','',{{ \Illuminate\Support\Js::from(t('Select an open order','চলমান অর্ডার নির্বাচন')) }}));select.firstChild.value='';
        for(const candidate of openOrders.values()){
            if(candidate.status==='cancelled' || (candidate.closed_at && Number(candidate.due)<=0))continue;
            const table=candidate.table?(locale==='bn'?(candidate.table.name_bn || candidate.table.name):candidate.table.name):words[candidate.service_type];
            const option=element('option','',`${candidate.order_no} · ${table || ''} · ${money(candidate.total)}`);option.value=candidate.id;select.append(option);
        }
        select.value=selected;$('rpos-open-count').textContent=number(select.options.length-1);
    }
    function renderProducts(){
        const search=$('rpos-search').value.trim().toLowerCase(),popular=category==='popular',rows=catalog.filter(product=>(popular?(!popularRank.size || popularRank.has(String(product.id))):!category || String(product.category)===category) && [product.name,product.variant,product.sku].join(' ').toLowerCase().includes(search));
        if(popular && popularRank.size)rows.sort((a,b)=>popularRank.get(String(a.id))-popularRank.get(String(b.id)));
        $('rpos-popular-hint').hidden=!popular || popularRank.size>0;
        for(const tab of $('rpos-categories').querySelectorAll('[data-category]')){tab.classList.toggle('active',tab.dataset.category===category);tab.setAttribute('aria-pressed',String(tab.dataset.category===category));}
        $('rpos-result').textContent=number(rows.length)+' '+words.foods; const holder=$('rpos-products'),focused=holder.contains(document.activeElement)?document.activeElement?.closest('[data-variant-id]')?.dataset.variantId:null;holder.replaceChildren();
        if($('rpos-menu-count'))$('rpos-menu-count').textContent=number(catalog.length);
        if(!rows.length)holder.append(element('div','rst-empty',words.noFoods));
        for(const product of rows){
            const inOrder=lines.filter(line=>String(line.variant_id)===String(product.id) && line.kitchen_status!=='void').reduce((total,line)=>total+Number(line.quantity),0);
            const node=button('','rpos-product'+(inOrder?' has-in-cart':''),()=>addProduct(product));node.dataset.variantId=product.id;node.disabled=!canEdit() || !permissions[order?'update':'create'] || busy || !!pending || !!stale || Number(product.stock)===0 && product.stock!==null;
            node.setAttribute('aria-label',[product.name,product.variant,money(product.price),words.addItem].filter(Boolean).join(' · '));
            if(product.image){const photo=element('div','rpos-product-photo'),image=element('img');image.src=product.image;image.alt='';image.loading='lazy';image.addEventListener('error',()=>{photo.remove();node.classList.add('no-photo');});photo.append(image);node.append(photo);}else node.classList.add('no-photo');
            if(inOrder){const badge=element('span','rpos-product-count',number(inOrder));badge.title=number(inOrder)+' '+words.inOrder;node.append(badge);}
            const info=element('div','rpos-product-info'),foot=element('div','rpos-product-foot'),plus=element('span','rpos-product-add','+');plus.setAttribute('aria-hidden','true');
            foot.append(element('strong','rpos-product-price',money(product.price)),plus);
            info.append(element('div','rpos-product-name',product.name));
            if(product.variant)info.append(element('div','rpos-product-meta',product.variant));
            info.append(foot);node.title=[product.name,product.variant,product.sku].filter(Boolean).join(' · ');
            if(product.stock!==null && Number(product.stock)<=0)info.append(element('span','rpos-product-meta',words.unavailable));node.append(info);holder.append(node);
        }
        if(focused)holder.querySelector('[data-variant-id="'+String(Number(focused))+'"]')?.focus({preventScroll:true});
    }
    function addProduct(product){
        if(!canEdit() || busy || pending || stale || root.dataset.step==='tables')return;
        if(!permissions[order?'update':'create']){alert(words.noPermission,true);return;}
        if(lines.length>=100){alert(words.limit,true);return;}
        const same=lines.find(line=>!line.id && String(line.variant_id)===String(product.id) && !line.kitchen_note && !(line.modifier_ids || []).length);
        if(same)same.quantity=Number(same.quantity)+1;
        else lines.push({variant_id:product.id,name:product.name,quantity:1,base_price:product.price,price:product.price,kitchen_status:'draft',kitchen_note:'',modifier_ids:[],modifiers:[]});
        markDirty();renderLines();
    }
    function linePrice(line){
        if(line.id && (!canEdit() || line.kitchen_status!=='draft'))return Number(line.price || 0);
        const product=products.get(String(line.variant_id)),base=Number(product?.price ?? line.base_price ?? line.price ?? 0);
        return base+modifiers.filter(modifier=>(line.modifier_ids || []).map(Number).includes(Number(modifier.id))).reduce((total,modifier)=>total+Number(modifier.price),0);
    }
    function renderLines(){
        const holder=$('rpos-lines');holder.replaceChildren();
        $('rpos-order-count').textContent=number(lines.filter(line=>line.kitchen_status!=='void').reduce((total,line)=>total+Number(line.quantity),0))+' '+words.foods;
        if(!lines.length){const empty=element('div','rpos-cart-empty');empty.append(decorativeIcon('receipt'),element('strong','',words.emptyTitle),element('p','',words.empty));holder.append(empty);}
        lines.forEach((line,index)=>{
            const status=line.kitchen_status || 'draft',editable=canEdit() && status==='draft' && permissions[order?'update':'create'] && !busy && !pending && !stale;
            const row=element('div','rpos-line'+(status==='void'?' void':'')),head=element('div','rpos-line-head'),description=element('div','rpos-line-description');
            const title=locale==='bn'?(line.name_bn || line.name):line.name,unit=element('span','rpos-line-unit',words.unitPrice+' '+money(linePrice(line))),amount=element('strong','rpos-line-amount',money(linePrice(line)*Number(line.quantity)));
            description.append(element('strong','rpos-line-name',title),unit);
            const quantity=element('div','rpos-quantity'+(!editable?' readonly':''));
            if(editable){
                const change=value=>{const next=Math.round(Number(value)*1000)/1000;if(!Number.isFinite(next)||next<=0 || next>999999){alert(words.invalid,true);return;}line.quantity=next;markDirty();renderLines();};
                const input=element('input','ret-input');input.type='number';input.value=line.quantity;input.min='.001';input.max='999999';input.step='.001';input.setAttribute('aria-label',words.quantity+' · '+title);input.addEventListener('change',()=>change(input.value));
                const minus=button('−','',()=>change(Math.max(.001,Number(line.quantity)-1))),plus=button('+','',()=>change(Number(line.quantity)+1));
                minus.setAttribute('aria-label','− '+words.quantity+' · '+title);plus.setAttribute('aria-label','+ '+words.quantity+' · '+title);quantity.append(minus,input,plus);
            }else quantity.append(element('span','',number(line.quantity)));
            head.append(description,quantity,amount);row.append(head);
            const extras=element('div','rpos-line-extras');
            if(status!=='draft' || workflow().kitchen_mode==='required'){const badge=element('span','rst-badge',status==='draft'?words.notSent:words[status] || status);badge.dataset.status=status;extras.append(badge);}
            if(editable){
                const options=element('details','rpos-line-options');options.append(element('summary','',words.optionsShort));
                const note=element('input','ret-input');note.value=line.kitchen_note || '';note.maxLength=500;note.placeholder=words.notes;note.setAttribute('aria-label',words.notes);note.addEventListener('input',()=>{line.kitchen_note=note.value;markDirty();});options.append(note);
                const product=products.get(String(line.variant_id)),choices=modifiers.filter(modifier=>!modifier.product_id || Number(modifier.product_id)===Number(product?.product_id));
                const modifierHolder=element('div','rpos-modifiers');
                for(const modifier of choices){const label=element('label','rpos-modifier'),checkbox=element('input');checkbox.type='checkbox';checkbox.checked=(line.modifier_ids || []).map(Number).includes(Number(modifier.id));checkbox.addEventListener('change',()=>{line.modifier_ids=checkbox.checked?[...(line.modifier_ids || []),Number(modifier.id)]:(line.modifier_ids || []).filter(id=>Number(id)!==Number(modifier.id));markDirty();unit.textContent=words.unitPrice+' '+money(linePrice(line));amount.textContent=money(linePrice(line)*Number(line.quantity));});label.append(checkbox,element('span','',modifier.name+' +'+money(modifier.price)));modifierHolder.append(label);}
                options.append(modifierHolder);extras.append(options);
                const remove=button('×','rpos-remove-item',()=>{lines.splice(index,1);markDirty();renderLines();});remove.setAttribute('aria-label',words.remove+' · '+title);remove.title=words.remove;extras.append(remove);
            }else{
                if(canEdit() && line.id && ['draft','queued'].includes(status) && permissions.delete)extras.append(button(words.voidItem,'rpos-small-btn danger',()=>voidLine(line)));
            }
            if(extras.childElementCount)row.append(extras);
            if(!editable){
                if(line.kitchen_note)row.append(element('p','rpos-note',line.kitchen_note));
                if(line.modifiers?.length)row.append(element('p','rpos-note',line.modifiers.map(modifier=>locale==='bn'?(modifier.name_bn || modifier.name):modifier.name).join(' · ')));
                if(line.void_reason)row.append(element('p','rpos-note',line.void_reason));
            }
            holder.append(row);
        });
    }
    function totals(){
        if(order && !dirty)return order;
        const subtotal=round(lines.filter(line=>line.kitchen_status!=='void').reduce((sum,line)=>sum+round(linePrice(line)*Number(line.quantity)),0));
        const discount=Math.min(subtotal,Math.max(0,Number(field('discount').value)||0)),base=round(subtotal-discount),option=field('tax_id').selectedOptions[0],rate=Number(option?.dataset.rate)||0,inclusive=option?.dataset.inclusive==='1';
        const tax=round(inclusive?base-base/(1+rate/100):base*rate/100),shipping=field('service_type').value==='delivery'?Math.max(0,Number(field('shipping').value)||0):0;
        return {subtotal,discount,tax,shipping,total:round(base+(inclusive?0:tax)+shipping)};
    }
    function renderTotals(){
        const values=totals();for(const [id,key] of [['rpos-subtotal','subtotal'],['rpos-discount-total','discount'],['rpos-tax-total','tax'],['rpos-shipping-total','shipping'],['rpos-total','total']])$(id).textContent=money(values[key]);
        $('rpos-total-label').textContent=order && !dirty?words.total:words.estimate;
        if(dirty && field('coupon_code').value.trim())$('rpos-discount-total').textContent='—';
        const adjusted=Number(values.discount)>0 || Number(values.tax)>0 || Number(values.shipping)>0 || !!field('coupon_code').value.trim();
        $('rpos-subtotal').parentElement.hidden=!adjusted;$('rpos-discount-total').parentElement.hidden=!Number(values.discount) && !field('coupon_code').value.trim();$('rpos-tax-total').parentElement.hidden=!Number(values.tax);$('rpos-shipping-total').parentElement.hidden=!Number(values.shipping);
        $('rpos-paid-row').hidden=!order || order.status!=='confirmed';$('rpos-due-row').hidden=!order || order.status!=='confirmed' || Number(order.due)<=.009;$('rpos-paid').textContent=money(order?.paid);$('rpos-due').textContent=money(order?.due);
        $('rpos-mobile-count').textContent=words.cart+' · '+number(lines.filter(line=>line.kitchen_status!=='void').reduce((total,line)=>total+Number(line.quantity),0));$('rpos-mobile-total').textContent=money(values.total);
    }
    function updateControls(){
        const lock=busy || !!pending || stale,editable=canEdit() && !lock,permission=permissions[order?'update':'create'],mode=workflow().kitchen_mode || 'optional',hasItems=lines.some(line=>line.kitchen_status!=='void');
        for(const input of form.querySelectorAll('input,select,textarea'))if(input.name!=='_token')input.disabled=!editable || !permission;
        for(const tab of $('rpos-service').children)tab.disabled=!editable || !permission;
        $('rpos-save').hidden=!canEdit();$('rpos-save').disabled=!editable || !permission || !lines.length;
        $('rpos-print-kot').disabled=lock || !hasItems || order?.status==='cancelled' || ((!order || dirty) && !permission);
        $('rpos-print-bill').disabled=lock || (!order && !hasItems) || ((!order || dirty) && !permission);
        const canSend=mode!=='off' && (canEdit()?lines.some(line=>(line.kitchen_status || 'draft')==='draft'):!!order?.can_send);
        $('rpos-send').hidden=!canSend;$('rpos-send').disabled=lock || !permission;
        $('rpos-checkout').textContent=payButtonText();
        $('rpos-checkout').hidden=order?.status==='cancelled' || (order?.status==='confirmed' && Number(order.due)<=0);$('rpos-checkout').disabled=lock || !permissions.create || !hasItems;
        $('rpos-cancel').hidden=!order || !canEdit() || !permissions.delete || lines.some(line=>['preparing','ready','served'].includes(line.kitchen_status));$('rpos-cancel').disabled=lock;
        $('rpos-serve').hidden=!order || !permissions.update || !order.can_serve;$('rpos-serve').disabled=lock || dirty;
        $('rpos-close').hidden=!order || !permissions.update || !order.can_close || !!order.closed_at;$('rpos-close').disabled=lock || dirty;
        const mobileClose=!$('rpos-close').hidden && Number(order?.due)<=.009;
        $('rpos-mobile-pay').textContent=mobileClose?words.closeTable:payButtonText();$('rpos-mobile-pay').hidden=$('rpos-checkout').hidden && !mobileClose;$('rpos-mobile-pay').disabled=mobileClose?$('rpos-close').disabled:$('rpos-checkout').disabled;
        $('rpos-workflow-label').textContent=words['kitchen_'+mode];$('rpos-workflow-label').title=mode==='required'?words.requiredHint:words.optionalHint;$('rpos-workflow-label').dataset.mode=mode;
        const customerLabel=field('customer_id').value?field('customer_id').selectedOptions[0]?.textContent || words.walkIn:field('customer_name').value.trim() || words.walkIn;
        $('rpos-customer-summary').textContent=customerLabel;$('rpos-customer-summary').title=customerLabel;
        $('rpos-new').disabled=lock || !permissions.create;$('rpos-open-load').disabled=lock;$('rpos-open').disabled=lock;
        $('rpos-draft-state').textContent=busy?words.saving:dirty?words.unsaved:order?words.saved:'';
        $('rpos-recovery').hidden=!pending && !stale;$('rpos-recovery-text').textContent=pending?words.connection:words.stale;$('rpos-retry').hidden=!pending;$('rpos-retry').disabled=busy;$('rpos-reload').hidden=!stale || !order;
        for(const action of ['receipt','kot']){$('rpos-'+action).hidden=true;$('rpos-'+action).href=order?route(action):'#';$('rpos-'+action).setAttribute('aria-disabled',String(lock));}
        $('rpos-order-more').hidden=$('rpos-send').hidden && $('rpos-cancel').hidden;root.dataset.locked=String(lock);root.dataset.emptyOrder=String(!order && !hasItems);syncStep();
        if($('rpos-payment').open){$('rpos-payment-close').disabled=busy || !!pending;for(const input of paymentForm.querySelectorAll('input,select,button'))input.disabled=busy || !!pending || stale;const retryPayment=pending && ['checkout','pay'].includes(pending.action);$('rpos-payment-submit').textContent=busy?words.processing:retryPayment?words.retry:order?.service_type==='takeaway' && payField('payment_mode').value==='full'?words.payNew:words.paymentConfirm;if(retryPayment && !busy)$('rpos-payment-submit').disabled=false;if(pending)$('rpos-payment-error').textContent=words.connection;else if(stale)$('rpos-payment-error').textContent=words.paymentChanged;}
        renderProducts();renderTables();
    }
    async function requestMutation(action,payload,url=null,method='POST'){
        if(busy)return false;
        floorEpoch++;
        if(!pending)pending={action,payload,url:url || route(action),method};
        busy=true;lastError='';persist();updateControls();alert(words.processing);
        try{
            const response=await fetch(pending.url,{method:pending.method,credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':field('_token').value},body:JSON.stringify(pending.payload)});
            let result={};try{result=await response.json();}catch(_){}
            if(!response.ok){
                if(response.status<500){pending=null;stale=response.status===409;persist();}
                throw Error(Object.values(result.errors || {}).flat()[0] || result.message || (stale?words.stale:words.failed));
            }
            if(result.success!==true || !result.order)throw Error(words.connection);
            const was=order?.id;pending=null;setOrder(result.order);checkoutKey=uuid();
            if(!was && order?.id){history.replaceState(null,'',routes.index+'?order='+order.id);}
            if(result.stocks)for(const product of catalog)if(Object.prototype.hasOwnProperty.call(result.stocks,String(product.id)))product.stock=result.stocks[String(product.id)];
            persist();alert(result.message || words.success);return result.order;
        }catch(error){lastError=pending?words.connection:error.message;alert(lastError,true);if(activePayment)$('rpos-payment-error').textContent=lastError;return false;}
        finally{busy=false;updateControls();renderLines();void refreshTables();}
    }
    async function save(send=false){
        if(!canEdit() || !permissions[order?'update':'create']){alert(words.noPermission,true);return false;}
        if(!validDraft())return false;
        const payload=snapshot();payload.send_to_kitchen=send;
        return requestMutation(order?'update':'store',payload,order?route('update'):routes.store,order?'PUT':'POST');
    }
    function validDraft(){
        if(!form.reportValidity()){alert(words.invalid,true);return false;}
        if(!lines.some(line=>line.kitchen_status!=='void')){alert(words.noItems,true);return false;}
        if(field('service_type').value==='dine_in' && !field('dining_table_id').value){alert(words.table,true);return false;}
        return true;
    }
    async function printDocument(kind){
        const control=$(kind==='kot'?'rpos-print-kot':'rpos-print-bill');
        if(busy || pending || stale || activePayment || control.disabled)return;
        if((!order || dirty) && !validDraft())return;
        const saved=order && !dirty?order:await save();
        if(!saved)return;
        window.RestaurantPosPrint?.open(route(kind),kind==='kot'?words.printKot:words.printBill);
    }
    async function loadOrder(id,force=false,refreshFloor=true){
        if(!id || busy || pending)return;
        if(dirty && !force && !confirm(words.discard))return;
        rememberRecovery();floorEpoch++;busy=true;updateControls();
        try{
            const response=await fetch(routes.show.replace('__ORDER__',id),{headers:{'Accept':'application/json'},credentials:'same-origin'}),body=await response.json();
            if(!response.ok || !body.order)throw Error(body.message || words.failed);
            browsingTables=false;setOrder(body.order);$('rpos-open-details').open=false;requestKey=uuid();reservationId=body.order.reservation_id || null;selectedReservationTableId=reservationId?body.order.dining_table_id:null;history.replaceState(null,'',routes.index+'?order='+id);persist();alert('');focusProducts();
        }catch(error){alert(error.message || words.failed,true);}finally{busy=false;updateControls();renderLines();if(refreshFloor)void refreshTables();}
    }
    async function voidLine(line){
        if(busy || pending || dirty){if(dirty)alert(words.saveFirst,true);return;}
        const reason=prompt(words.voidReason);if(!reason?.trim())return;
        await requestMutation('void',{version:order.version,reason:reason.trim()},route('void',line.id));
    }
    async function openPayment(){
        if(pending || busy || stale || !permissions.create)return;
        if(canEdit() && (dirty || !order) && !await save())return;
        if(!order)return;
        if(order.status==='confirmed' && Number(order.due)<=0){alert(words.paid);return;}
        activePayment=true;checkoutKey=uuid();payField('payment_mode').value='full';payField('payment_mode').querySelector('[value=due]').hidden=order.status==='confirmed';payField('method').value='cash';payField('payment_reference').value='';payField('tendered').value=Number(order.status==='confirmed'?order.due:order.total).toFixed(2);
        $('rpos-pay-total').textContent=money(order.status==='confirmed'?order.due:order.total);$('rpos-payment-error').textContent='';$('rpos-payment').showModal();updatePayment();
    }
    function paymentValues(){
        const total=Number(order?.status==='confirmed'?order.due:order?.total || 0),mode=payField('payment_mode').value,selected=payField('method').selectedOptions[0],method=selected?.dataset.method || 'cash',tendered=Number(payField('tendered').value);
        const amount=mode==='due'?0:mode==='partial'?tendered:Math.min(tendered,total);let error='';
        if(!Number.isFinite(tendered)||tendered<0 || (mode==='full' && (tendered<total || (method!=='cash' && Math.abs(tendered-total)>.009))) || (mode==='partial' && (tendered<=0 || tendered>=total)))error=words.amount;
        else if(total-amount>.009 && !order?.customer_id)error=words.customer;
        return {total,mode,method,tendered,amount,error,configuredId:selected?.dataset.id || null};
    }
    function updatePayment(){
        if(!order)return;
        if(payField('payment_mode').value==='due')payField('tendered').value='0';
        payField('tendered').readOnly=payField('payment_mode').value==='due';const payment=paymentValues();
        $('rpos-change').textContent=money(payment.mode==='full' && payment.method==='cash'?Math.max(0,payment.tendered-payment.total):0);$('rpos-payment-due').textContent=money(Math.max(0,payment.total-payment.amount));$('rpos-payment-error').textContent=stale?words.paymentChanged:payment.error;$('rpos-payment-submit').disabled=busy || !!pending || stale || !!payment.error;
        $('rpos-payment-submit').textContent=order.service_type==='takeaway' && payment.mode==='full'?words.payNew:words.paymentConfirm;
    }
    $('rpos-payment-form').addEventListener('submit',async event=>{
        event.preventDefault();if(busy || !order)return;if(pending){if(['checkout','pay'].includes(pending.action))await retryPending();return;}if(stale || !paymentForm.reportValidity())return;
        const values=paymentValues();if(values.error){updatePayment();return;}
        const payload={version:order.version,expected_total:order.total,request_key:checkoutKey,payment_mode:values.mode,payment_method:values.method==='cash'?'cash':'bank',payment_method_id:values.configuredId,tendered:values.tendered,payment_reference:payField('payment_reference').value};
        const result=await requestMutation(order.status==='confirmed'?'pay':'checkout',payload);
        if(result)finishPayment(result,payload);else if(!pending){updatePayment();$('rpos-payment-error').textContent=lastError;}
    });
    $('rpos-payment-close').onclick=()=>{if(!busy && !pending){activePayment=false;$('rpos-payment').close();void refreshTables();}};
    $('rpos-payment').addEventListener('cancel',event=>{if(busy || pending)event.preventDefault();else{activePayment=false;void refreshTables();}});
    for(const input of paymentForm.querySelectorAll('input,select'))input.addEventListener('input',updatePayment);
    $('rpos-quick').onclick=event=>{const node=event.target.closest('[data-amount]');if(!node || busy || pending)return;payField('tendered').value=node.dataset.amount==='exact'?Number(order.status==='confirmed'?order.due:order.total).toFixed(2):node.dataset.amount;updatePayment();};
    $('rpos-save').onclick=()=>save();$('rpos-send').onclick=async()=>{if(busy || pending || stale || $('rpos-send').disabled)return;if(dirty || !order)await save(true);else await requestMutation('send',{version:order.version});};$('rpos-checkout').onclick=openPayment;$('rpos-print-kot').onclick=()=>printDocument('kot');$('rpos-print-bill').onclick=()=>printDocument('receipt');
    $('rpos-receipt').onclick=event=>{event.preventDefault();void printDocument('receipt');};$('rpos-kot').onclick=event=>{event.preventDefault();void printDocument('kot');};
    $('rpos-serve').onclick=()=>{if(order && confirm(words.serveConfirm))requestMutation('serve',{version:order.version});};
    $('rpos-close').onclick=()=>{if(order && confirm((Number(order.due)>.009?words.balanceWarning+' '+money(order.due)+'\n':'')+words.closeConfirm))requestMutation('close',{version:order.version});};
    $('rpos-cancel').onclick=()=>{if(!order)return;const reason=prompt(words.cancelReason);if(reason?.trim())requestMutation('cancel',{version:order.version,reason:reason.trim()});};
    async function retryPending(){if(!pending || busy)return;const saved=pending;const result=await requestMutation(saved.action,saved.payload,saved.url,saved.method);if(result && ['pay','checkout'].includes(saved.action))finishPayment(result,saved.payload);}
    $('rpos-retry').onclick=retryPending;
    $('rpos-reload').onclick=()=>{if(order && confirm(words.reload))loadOrder(order.id,true);};
    $('rpos-open-load').onclick=()=>loadOrder($('rpos-open').value);
    $('rpos-new').onclick=()=>beginNewOrder();
    $('rpos-change-table').onclick=()=>{if(busy || pending || stale || activePayment || field('service_type').value!=='dine_in')return;browsingTables=true;serviceFields();persist();$('rpos-table-search').focus({preventScroll:true});};
    $('rpos-continue-order').onclick=()=>{if(busy || pending || stale || activePayment || !field('dining_table_id').value)return;browsingTables=false;serviceFields();persist();focusProducts();};
    document.addEventListener('click',event=>{if(!$('rpos-open-details').contains(event.target))$('rpos-open-details').open=false;});
    $('rpos-mobile-cart').onclick=()=>root.querySelector('.rpos-order-panel').scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});
    $('rpos-mobile-pay').onclick=()=>{if(!$('rpos-close').hidden && Number(order?.due)<=.009)$('rpos-close').click();else openPayment();};
    $('rpos-last-sale-dismiss').onclick=()=>{lastSale=null;try{localStorage.removeItem(storageKey+'-last-sale');}catch(_){}renderLastSale();};
    $('rpos-service').onclick=event=>{const node=event.target.closest('[data-service]');if(!node || node.disabled || !canEdit() || busy || pending || stale || !permissions[order?'update':'create'])return;const was=field('service_type').value;if(was!==node.dataset.service)browsingTables=node.dataset.service==='dine_in';field('service_type').value=node.dataset.service;if(node.dataset.service==='delivery' && $('rpos-guest-details'))$('rpos-guest-details').open=true;serviceFields();markDirty();};
    $('rpos-search').addEventListener('input',renderProducts);$('rpos-categories').onclick=event=>{const node=event.target.closest('[data-category]');if(!node)return;category=node.dataset.category;for(const tab of $('rpos-categories').children)tab.classList.toggle('active',tab===node);renderProducts();};
    $('rpos-table-search')?.addEventListener('input',renderTables);
    $('rpos-table-areas')?.addEventListener('click',event=>{const node=event.target.closest('[data-area]');if(!node)return;tableArea=node.dataset.area;for(const tab of $('rpos-table-areas').querySelectorAll('[data-area]')){tab.classList.toggle('active',tab===node);tab.setAttribute('aria-pressed',String(tab===node));}renderTables();});
    $('rpos-refresh-tables')?.addEventListener('click',()=>void refreshTables());
    window.addEventListener('focus',()=>void refreshTables());
    document.addEventListener('visibilitychange',()=>{if(!document.hidden)void refreshTables();});
    form.addEventListener('submit',event=>event.preventDefault());for(const input of form.querySelectorAll('input,select,textarea'))if(input.name!=='_token')input.addEventListener('input',markDirty);
    const languageForm=document.querySelector('.language-form');languageForm?.addEventListener('submit',event=>{if(busy || pending){event.preventDefault();alert(words.connection,true);return;}persist();});window.addEventListener('pagehide',persist);window.addEventListener('beforeunload',event=>{if(pending || busy){event.preventDefault();event.returnValue='';}});
    setOrder(initialOrder);
    if(!initialOrder){
        if(reservation){field('service_type').value='dine_in';for(const name of ['dining_table_id','guest_count','customer_name','customer_phone'])field(name).value=reservation[name] ?? '';}
        else if(initialTable){field('service_type').value='dine_in';field('dining_table_id').value=String(initialTable);}
        serviceFields();
    }
    try{
        const stored=JSON.parse(localStorage.getItem(storageKey) || 'null');
        if(stored && String(stored.orderId || '')===String(order?.id || '') && String(stored.reservationId || '')===String(reservationId || '')){
            if(Number(stored.baseVersion || 0)===Number(order?.version || 0)){
                requestKey=stored.requestKey || requestKey;pending=stored.pending || null;browsingTables=stored.browsingTables===true;selectedReservationTableId=reservationId?(stored.data?.dining_table_id || selectedReservationTableId):null;
                if(stored.dirty){for(const name of fields)if(stored.data?.[name]!==undefined)field(name).value=stored.data[name] ?? '';lines=(stored.data.items || []).map(line=>{const previous=order?.items?.find(item=>Number(item.id)===Number(line.id));const product=products.get(String(line.variant_id));return {...(previous || {name:product?.name || '',price:product?.price || 0,base_price:product?.price || 0,kitchen_status:'draft'}),...line};});dirty=true;alert(words.restored);}
            }else if(stored.dirty || stored.pending){rememberRecovery();alert(words.stale,true);}
        }
    }catch(_){}
    try{lastSale=JSON.parse(localStorage.getItem(storageKey+'-last-sale') || 'null');}catch(_){}renderLastSale();
    if(field('service_type').value==='delivery' && $('rpos-guest-details'))$('rpos-guest-details').open=true;
    serviceFields();renderOpen();renderLines();renderTotals();updateControls();
    const floorTimer=setInterval(()=>{if(!root.isConnected){clearInterval(floorTimer);return;}void refreshTables();},15000);
})();
</script>
