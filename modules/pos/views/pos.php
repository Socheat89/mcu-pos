<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$urlPrefix = mc_base_path();
$subdomain = Tenant::getCurrent()['subdomain'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="<?php echo mc_base_path(); ?>/public/js/bakong-khqr.js?v=<?php echo time(); ?>"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Space Grotesk"', '"Battambang"', 'sans-serif'],
                        display: ['"Space Grotesk"', '"Battambang"', 'sans-serif']
                    },
                    keyframes: {
                        fadeUp: { '0%': { opacity: '0', transform: 'translateY(12px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideIn: { '0%': { opacity: '0', transform: 'translateX(32px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } }
                    },
                    animation: {
                        fadeUp: 'fadeUp 0.45s ease-out both',
                        fadeIn: 'fadeIn 0.6s ease-out both',
                        slideIn: 'slideIn 0.35s ease-out both'
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --pos-bg: #f6f4ef;
            --pos-card: #ffffff;
            --pos-text: #1f2937;
            --pos-text-muted: #6b7280;
            --pos-muted: #6b7280;
            --pos-border: #e5e7eb;
            --pos-border-light: rgba(148, 163, 184, 0.4);
            --pos-primary: #0f766e;
            --pos-primary-light: #ccfbf1;
            --pos-secondary: #0ea5e9;
            --pos-accent: #f59e0b;
            --pos-success: #16a34a;
            --pos-warning: #f59e0b;
            --pos-danger: #ef4444;
            --pos-brand-a: #0f766e;
            --pos-brand-b: #f59e0b;
            --pos-gradient-primary: linear-gradient(135deg, #0f766e 0%, #14b8a6 55%, #f59e0b 100%);
            --pos-gradient-surface: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            --pos-gradient-glass: linear-gradient(140deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.35));
            --pos-gradient-indigo: linear-gradient(135deg, #0f766e 0%, #f59e0b 100%);
        }

        body.pos-app {
            font-family: 'Space Grotesk', 'Battambang', sans-serif;
            background: var(--pos-bg);
        }
    </style>

</head>
<body class="pos-app">
    <?php $activeNav = 'pos'; include __DIR__ . '/partials/navbar.php'; ?>
    <?php
        $productCount = is_array($products) ? count($products) : 0;
        $lowStockCount = 0;
        if (is_array($products)) {
            foreach ($products as $productRow) {
                $qty = (int)($productRow['stock_quantity'] ?? 0);
                if ($qty > 0 && $qty <= 5) {
                    $lowStockCount++;
                }
            }
        }
    ?>

    <div class="relative mx-auto overflow-hidden rounded-[38px] border border-white/75 bg-[#fbfaf7] shadow-[0_28px_80px_rgba(15,23,42,0.10)] animate-fadeIn" style="max-width: 1620px; min-height: calc(100vh - 190px);">
        <div class="pointer-events-none absolute -left-10 top-10 h-40 w-40 rounded-full border border-slate-200/70"></div>
        <div class="pointer-events-none absolute -right-10 top-8 h-28 w-28 rounded-full bg-slate-200/70"></div>
        <div class="pointer-events-none absolute -left-20 bottom-0 h-52 w-52 rounded-full border border-slate-200/70"></div>

        <div class="grid min-h-[calc(100vh-190px)] grid-cols-1 xl:grid-cols-[88px_minmax(0,1fr)_420px]">
            <aside class="border-b border-slate-200 bg-white/90 xl:border-b-0 xl:border-r xl:border-slate-200">
                <div class="flex h-full flex-row items-center justify-between gap-3 px-4 py-3 xl:flex-col xl:justify-start xl:py-5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg">
                        <i class="fas fa-hexagon-nodes"></i>
                    </div>
                    <nav class="flex flex-row gap-2 xl:mt-8 xl:flex-col">
                        <button class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg" type="button"><i class="fas fa-cart-shopping"></i></button>
                        <button class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" type="button" onclick="toggleMenuOrders()"><i class="fas fa-clipboard-list"></i></button>
                        <button class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" type="button"><i class="fas fa-qrcode"></i></button>
                        <button class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" type="button"><i class="fas fa-layer-group"></i></button>
                        <button class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" type="button"><i class="fas fa-gear"></i></button>
                    </nav>
                    <div class="mt-auto hidden xl:flex flex-col items-center gap-2 pb-1 pt-6">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500"><i class="fas fa-lock"></i></div>
                    </div>
                </div>
            </aside>

            <section class="border-b border-slate-200 bg-white/70 px-4 py-4 xl:border-b-0 xl:border-r xl:border-slate-200 xl:px-6 xl:py-5">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[240px]">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="search" placeholder="<?php echo __('search_placeholder'); ?>" autocomplete="off" class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm font-semibold text-slate-800 shadow-sm transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">
                    </div>
                    <div class="relative min-w-[180px]">
                        <select id="category" class="w-full appearance-none rounded-2xl border border-slate-200 bg-white py-3 pl-4 pr-10 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-slate-400 focus:ring-4 focus:ring-slate-100">
                            <option value=""><?php echo __('all_categories'); ?></option>
                        </select>
                        <i class="fas fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    </div>
                    <button class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50" onclick="toggleMenuOrders()">
                        <i class="fas fa-list-check text-slate-500"></i>
                        <span><?php echo __('pending_orders'); ?></span>
                        <?php if (count($pendingMenuOrders) > 0): ?>
                            <span class="rounded-full bg-slate-900 px-2 py-0.5 text-[11px] font-bold text-white"><?php echo count($pendingMenuOrders); ?></span>
                        <?php endif; ?>
                    </button>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">Products</div>
                                <div class="mt-1 text-2xl font-extrabold text-slate-900"><?php echo (int)$productCount; ?></div>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"><i class="fas fa-cubes"></i></div>
                        </div>
                    </div>
                    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">Cart items</div>
                                <div id="cartMetric" class="mt-1 text-2xl font-extrabold text-slate-900">0</div>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"><i class="fas fa-bag-shopping"></i></div>
                        </div>
                    </div>
                    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">Low stock</div>
                                <div class="mt-1 text-2xl font-extrabold text-slate-900"><?php echo (int)$lowStockCount; ?></div>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"><i class="fas fa-triangle-exclamation"></i></div>
                        </div>
                    </div>
                    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">Terminal</div>
                                <div class="mt-1 text-lg font-extrabold text-slate-900">Ready</div>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"><i class="fas fa-bolt"></i></div>
                        </div>
                    </div>
                </div>

                <?php if (isset($resumeOrder) && $resumeOrder): ?>
                    <div class="mt-4 flex flex-wrap items-center gap-4 rounded-[22px] border border-slate-200 bg-slate-50 px-4 py-3 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white shadow">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">Continuing Order</div>
                            <div class="text-base font-extrabold text-slate-900">Reference #<?php echo (int)$resumeOrder['id']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-4 flex items-center gap-2 overflow-x-auto pb-1 text-sm font-semibold text-slate-500">
                    <button class="rounded-full bg-slate-900 px-4 py-2 text-white">All</button>
                    <button class="rounded-full border border-slate-200 bg-white px-4 py-2">Woman</button>
                    <button class="rounded-full border border-slate-200 bg-white px-4 py-2">Man</button>
                    <button class="rounded-full border border-slate-200 bg-white px-4 py-2">New in</button>
                    <button class="rounded-full border border-slate-200 bg-white px-4 py-2">Sale</button>
                </div>

                <div id="products" class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-3 2xl:grid-cols-4"></div>
            </section>

            <aside class="flex h-full flex-col bg-white/95 px-4 py-4 xl:px-5 xl:py-5">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-slate-400"><?php echo __('billing_cart'); ?></p>
                        <h2 class="text-xl font-extrabold text-slate-900"><?php echo __('billing_cart'); ?></h2>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700">#0073</div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Subtotal</div>
                        <div id="cartMetricSubtotal" class="mt-1 font-extrabold text-slate-900">$0.00</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Quick mode</div>
                        <div class="mt-1 font-extrabold text-slate-900">Cash</div>
                    </div>
                </div>

                <div id="cart" class="mt-4 flex min-h-[240px] flex-1 flex-col gap-3 overflow-y-auto pr-1"></div>

                <form id="checkoutForm" method="POST" action="<?php echo mc_url($subdomain . '/pos/orders/create'); ?>" class="mt-6 space-y-4">
                    <?php if (isset($resumeOrder) && $resumeOrder): ?>
                        <input type="hidden" name="resume_order_id" value="<?php echo (int)$resumeOrder['id']; ?>">
                    <?php endif; ?>
                    <input type="hidden" id="itemsPayload" value="">
                    <input type="hidden" name="order_status" id="order_status" value="completed">
                    <input type="hidden" name="payment_method" id="payment_method" value="cash">
                    <input type="hidden" name="cash_given" id="cash_given" value="">

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400"><?php echo __('customer'); ?></label>
                            <div class="relative mt-2">
                                <select name="customer_id" id="customer" class="w-full appearance-none rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value=""><?php echo __('walk_in_customer'); ?></option>
                                    <?php foreach ($customers as $customer1): ?>
                                        <option value="<?php echo (int)$customer1['id']; ?>"><?php echo htmlspecialchars($customer1['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400"><?php echo __('order_type'); ?></label>
                            <div class="relative mt-2">
                                <select id="terminal_order_status" class="w-full appearance-none rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="completed"><?php echo __('sale'); ?></option>
                                    <option value="pending"><?php echo __('hold'); ?></option>
                                </select>
                                <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between text-sm font-semibold text-slate-500">
                            <span><?php echo __('subtotal'); ?></span>
                            <span id="subtotal_pre">$0.00</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm font-semibold text-slate-500">
                            <span><?php echo __('tax'); ?></span>
                            <span>$0.00</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-dashed border-slate-200 pt-3 text-lg font-extrabold text-slate-900">
                            <span><?php echo __('grand_total'); ?></span>
                            <span id="subtotal" class="text-emerald-700">$0.00</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:text-rose-600" type="button" onclick="clearCart()" title="<?php echo __('cancel'); ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button class="group flex flex-1 items-center justify-between rounded-2xl bg-slate-900 px-5 py-4 text-base font-extrabold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50" type="button" id="btnPay">
                             <span><?php echo __('complete_checkout'); ?></span>
                             <i class="fas fa-arrow-right transition group-hover:translate-x-1"></i>
                        </button>
                    </div>
                </form>
            </aside>
        </div>
    </div>


    <div id="paymentModal" class="pos-modal-overlay">
        <div class="w-full max-w-lg rounded-[28px] border border-slate-200 bg-white/95 p-6 shadow-2xl backdrop-blur">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900"><?php echo __('checkout_summary'); ?></h3>
                        <p class="text-xs font-semibold text-slate-500"><?php echo __('secure_checkout_msg'); ?></p>
                    </div>
                </div>
                <button class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100" onclick="closePaymentModal()"><i class="fas fa-times"></i></button>
            </div>

            <div class="mt-6 space-y-4">
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?php echo __('total_payable'); ?></div>
                        <div id="modal_subtotal" class="text-2xl font-extrabold text-slate-900">$0.00</div>
                    </div>
                    <div class="text-right">
                        <div id="clock_now" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"></div>
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400"><?php echo __('payment_instrument'); ?></label>
                    <div class="mt-3 grid grid-cols-3 gap-3">
                        <?php if ($settings['pos_method_cash_enabled'] == '1'): ?>
                        <div class="payment-method-item flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50" data-method="cash" onclick="selectPaymentMethod('cash')">
                            <i class="fas fa-wallet text-lg"></i>
                            <span><?php echo __('cash'); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($settings['pos_method_khqr_enabled'] == '1'): ?>
                        <div class="payment-method-item flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50" data-method="khqr" onclick="selectPaymentMethod('khqr')">
                            <i class="fas fa-qrcode text-lg"></i>
                            <span><?php echo __('khqr'); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($settings['pos_method_card_enabled'] == '1'): ?>
                        <div class="payment-method-item flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50" data-method="card" onclick="selectPaymentMethod('card')">
                            <i class="fas fa-credit-card text-lg"></i>
                            <span><?php echo __('card'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="cashAmountGroup" class="space-y-3">
                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400"><?php echo __('cash_received'); ?></label>
                        <div class="relative mt-2">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-base font-bold text-slate-500">$</span>
                            <input id="modal_cash_given" class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-8 pr-4 text-lg font-bold text-slate-800 shadow-sm transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" type="number" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <div id="changeGroup" class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50/70 px-4 py-3">
                        <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700"><?php echo __('balance_change'); ?></span>
                        <span id="modal_change" class="text-xl font-extrabold text-emerald-700">$0.00</span>
                    </div>
                </div>

                <div id="khqrGroup" class="hidden rounded-2xl border border-slate-200 bg-white p-5 text-center">
                    <div id="qrcode_container" class="mx-auto inline-block rounded-2xl border border-slate-200 bg-white p-3"></div>
                    <div class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-rose-600"><?php echo __('waiting_for_scan'); ?></div>
                </div>

                <div id="cardGroup" class="hidden rounded-2xl border border-slate-200 bg-white p-6 text-center">
                    <i class="fas fa-terminal text-2xl text-emerald-600"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-600"><?php echo __('connect_terminal_msg'); ?></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <button class="w-full rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl" onclick="confirmPayment()">
                    <?php echo __('complete_payment'); ?> <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.2em] text-slate-500 transition hover:bg-slate-50" onclick="closePaymentModal()"><?php echo __('discard_checkout'); ?></button>
            </div>
        </div>
    </div>

    <!-- Menu Orders Side Panel -->
    <div id="menuOrdersPanel" class="pos-modal-overlay items-stretch justify-end p-0">
        <div class="flex h-full w-full max-w-[440px] flex-col bg-white shadow-2xl animate-slideIn">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-extrabold text-slate-900"><?php echo __('pending_orders'); ?></h3>
                <button class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100" onclick="toggleMenuOrders()"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="flex-1 space-y-3 overflow-y-auto p-5">
                <?php if (empty($pendingMenuOrders)): ?>
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center text-sm font-semibold text-slate-500">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-200/70 text-slate-500">
                            <i class="fas fa-inbox text-xl"></i>
                        </div>
                        <p>No pending orders</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingMenuOrders as $mo): ?>
                        <div class="group cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg" onclick="window.location.href='?resume=<?php echo $mo['id']; ?>'">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <span class="text-base font-extrabold text-slate-900">#<?php echo $mo['id']; ?></span>
                                    <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($mo['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="text-lg font-extrabold text-slate-900">$<?php echo number_format($mo['total'], 2); ?></div>
                            </div>
                            
                            <?php if (!empty($mo['notes'])): ?>
                                <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/70 px-3 py-2">
                                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-700">Location/Note</div>
                                    <div class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($mo['notes']); ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-500">
                                    <?php echo (int)$mo['item_lines']; ?> Items
                                </div>
                                <button class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold uppercase tracking-[0.15em] text-white shadow transition hover:bg-emerald-700">
                                    Resume Sale <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="border-t border-slate-200 px-6 py-5">
                <button class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.2em] text-slate-500 transition hover:bg-slate-50" onclick="toggleMenuOrders()">Close View</button>
            </div>
        </div>
    </div>



    <script>
        const PRODUCTS = <?php echo json_encode(array_map(function($p) {
            $image = !empty($p['image'])
                ? mc_url('uploads/products/' . $p['image'])
                : mc_url('public/images/no-image.svg');
            return [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'sku' => $p['sku'] ?? '',
                'barcode' => $p['barcode'] ?? '',
                'price' => (float)$p['price'],
                'stock' => (int)$p['stock_quantity'],
                'category' => $p['category_name'] ?? 'No Category',
                'image' => $image
            ];
        }, $products)); ?>;

        const cart = new Map(); // productId -> { product, qty }

        const RESUME = <?php
            $resumePayload = null;
            if (isset($resumeOrder) && $resumeOrder) {
                $resumePayload = [
                    'id' => (int)$resumeOrder['id'],
                    'customer_id' => $resumeOrder['customer_id'] !== null ? (int)$resumeOrder['customer_id'] : null,
                    'items' => array_map(function($it) {
                        return [
                            'product_id' => (int)($it['product_id'] ?? 0),
                            'quantity' => (int)($it['quantity'] ?? 0),
                        ];
                    }, $resumeOrder['items'] ?? [])
                ];
            }
            echo json_encode($resumePayload);
        ?>;

        const els = {
            products: document.getElementById('products'),
            cart: document.getElementById('cart'),
            search: document.getElementById('search'),
            category: document.getElementById('category'),
            subtotal: document.getElementById('subtotal'),
            subtotalPre: document.getElementById('subtotal_pre'),
            cartCount: document.getElementById('cartCount'),
            cartMetric: document.getElementById('cartMetric'),
            cartMetricSubtotal: document.getElementById('cartMetricSubtotal'),
            orderStatus: document.getElementById('order_status'),
            terminalOrderStatus: document.getElementById('terminal_order_status'),
            paymentMethod: document.getElementById('payment_method'),
            cashGiven: document.getElementById('cash_given'),
            checkoutForm: document.getElementById('checkoutForm'),
            btnPay: document.getElementById('btnPay'),
            paymentModal: document.getElementById('paymentModal'),
            modalCashGiven: document.getElementById('modal_cash_given'),
            modalSubtotal: document.getElementById('modal_subtotal'),
            modalChange: document.getElementById('modal_change')
        };

        function money(value) {
            return '$' + (Math.round(value * 100) / 100).toFixed(2);
        }

        function computeSubtotal() {
            let total = 0;
            for (const { product, qty } of cart.values()) {
                total += product.price * qty;
            }
            return total;
        }

        function renderCategories() {
            const set = new Set(PRODUCTS.map(p => p.category || 'No Category'));
            const cats = Array.from(set).sort((a,b) => a.localeCompare(b));
            for (const c of cats) {
                const opt = document.createElement('option');
                opt.value = c;
                opt.textContent = c;
                els.category.appendChild(opt);
            }
        }

        function filteredProducts() {
            const q = els.search.value.trim().toLowerCase();
            const cat = els.category.value;
            return PRODUCTS.filter(p => {
                const matchesCat = !cat || (p.category === cat);
                if (!matchesCat) return false;
                if (!q) return true;
                return (
                    (p.name || '').toLowerCase().includes(q) ||
                    (p.sku || '').toLowerCase().includes(q) ||
                    (p.barcode || '').toLowerCase().includes(q)
                );
            });
        }

        function renderProducts() {
            const list = filteredProducts();
            els.products.innerHTML = '';

            if (!list.length) {
                els.products.innerHTML = `
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white/60 p-10 text-center text-sm font-semibold text-slate-500">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                            <i class="fas fa-box-open text-xl"></i>
                        </div>
                        No products found with those filters.
                    </div>
                `;
                return;
            }

            list.forEach((p, index) => {
                const div = document.createElement('div');
                div.className = 'group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-xl';
                div.classList.add('animate-fadeUp');
                div.style.animationDelay = `${Math.min(index * 0.03, 0.4)}s`;

                const disabled = p.stock <= 0;
                const stockClasses = disabled
                    ? 'border-rose-200 bg-rose-50 text-rose-600'
                    : 'border-slate-200 bg-white/90 text-slate-500';
                const meta = p.sku ? escapeHtml(p.sku) : (p.category ? escapeHtml(p.category) : '');

                div.innerHTML = `
                    <span class="absolute right-3 top-3 rounded-full border ${stockClasses} px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em]">
                        ${disabled ? '<?php echo __('out_of_stock'); ?>' : p.stock + ' <?php echo __('in_stock'); ?>'}
                    </span>
                    <div class="aspect-square w-full overflow-hidden rounded-2xl bg-slate-100/80 ring-1 ring-inset ring-slate-200/70">
                        ${p.image && !p.image.includes('no-image.svg')
                            ? `<img src="${p.image}" alt="${escapeHtml(p.name)}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">`
                            : `<div class="flex h-full w-full items-center justify-center text-slate-300"><i class="fas fa-image text-2xl"></i></div>`
                        }
                    </div>
                    <div class="flex flex-1 flex-col px-1 pb-3 pt-3">
                        <div class="min-h-[40px] text-sm font-semibold leading-snug text-slate-800">${escapeHtml(p.name)}</div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-lg font-black text-emerald-700">${money(p.price)}</span>
                            <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">${meta || ''}</span>
                        </div>
                    </div>
                `;

                if (!disabled) {
                    div.classList.add('cursor-pointer');
                    div.addEventListener('click', () => addToCart(p.id, 1));
                } else {
                    div.classList.add('opacity-60', 'cursor-not-allowed');
                }
                els.products.appendChild(div);
            });
        }

        function setPayEnabled(enabled) {
            if (!els.btnPay) return;
            els.btnPay.disabled = !enabled;
            els.btnPay.setAttribute('aria-disabled', enabled ? 'false' : 'true');
        }

        function renderCart() {
            els.cart.innerHTML = '';

            if (!cart.size) {
                els.cart.innerHTML = `
                    <div class="flex h-full flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-6 text-center">
                        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i class="fas fa-basket-shopping text-xl"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-600"><?php echo __('cart_empty'); ?></p>
                        <p class="mt-1 text-xs text-slate-400"><?php echo __('select_products'); ?></p>
                    </div>
                `;
                setPayEnabled(false);
            } else {
                setPayEnabled(true);
                for (const [productId, entry] of cart.entries()) {
                    const p = entry.product;
                    const qty = entry.qty;

                    const item = document.createElement('div');
                    item.className = 'flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm';
                    item.innerHTML = `
                        <div class="h-12 w-12 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-inset ring-slate-200">
                            ${p.image && !p.image.includes('no-image.svg')
                                ? `<img src="${p.image}" class="h-full w-full object-cover">`
                                : `<div class="flex h-full w-full items-center justify-center text-slate-300"><i class="fas fa-image text-sm"></i></div>`
                            }
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800">${escapeHtml(p.name)}</div>
                            <div class="text-xs font-bold text-emerald-700">${money(p.price)}</div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-slate-100 px-2 py-1">
                            <button type="button" class="minus flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm transition hover:bg-emerald-600 hover:text-white">-</button>
                            <span class="w-6 text-center text-sm font-semibold text-slate-700">${qty}</span>
                            <button type="button" class="plus flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm transition hover:bg-emerald-600 hover:text-white">+</button>
                        </div>
                    `;

                    item.querySelector('.minus').addEventListener('click', () => setQty(productId, qty - 1));
                    item.querySelector('.plus').addEventListener('click', () => setQty(productId, qty + 1));

                    els.cart.appendChild(item);
                }
            }

            const subtotal = computeSubtotal();
            if (els.subtotal) els.subtotal.textContent = money(subtotal);
            if (els.subtotalPre) els.subtotalPre.textContent = money(subtotal);

            const itemCount = Array.from(cart.values()).reduce((acc, x) => acc + x.qty, 0);
            if (els.cartCount) els.cartCount.textContent = itemCount + ' <?php echo __('items'); ?>';
            if (els.cartMetric) els.cartMetric.textContent = itemCount;
            if (els.cartMetricSubtotal) els.cartMetricSubtotal.textContent = money(subtotal);

            syncFormItems();
        }

        function setQty(productId, qty) {
            const p = PRODUCTS.find(x => x.id === productId);
            if (!p) return;

            if (qty <= 0) {
                cart.delete(productId);
            } else {
                const maxQty = Math.max(0, p.stock);
                if (qty > maxQty) {
                    if (window.POSUI && window.POSUI.toast) {
                        window.POSUI.toast({ type: 'warning', title: '<?php echo __('low_stock'); ?>', message: 'Only ' + maxQty + ' available.' });
                    }
                    qty = maxQty;
                }
                cart.set(productId, { product: p, qty });
            }
            renderCart();
        }

        function addToCart(productId, deltaQty) {
            const p = PRODUCTS.find(x => x.id === productId);
            if (!p) return;
            const existing = cart.get(productId);
            const nextQty = (existing ? existing.qty : 0) + deltaQty;
            setQty(productId, nextQty);
        }

        function clearCart() {
            if (cart.size === 0) return;
            if (confirm('<?php echo __('confirm_clear_cart', ['default' => 'Are you sure you want to clear the cart?']); ?>')) {
                cart.clear();
                renderCart();
            }
        }

        function tryQuickAddFirstMatch() {
            const list = filteredProducts();
            if (!list.length) return;
            const p = list[0];
            if (p.stock <= 0) return;
            addToCart(p.id, 1);
            els.search.value = '';
            renderProducts();
        }

        // Modal functions
        function showPaymentModal() {
            if (cart.size === 0) return;
            const subtotal = computeSubtotal();
            els.modalSubtotal.textContent = money(subtotal);
            
            // Start clock
            updateClock();
            window.paymentClock = setInterval(updateClock, 1000);
            
            els.modalCashGiven.value = '';
            updateModalChange();
            els.paymentModal.style.display = 'flex';
            selectPaymentMethod('cash'); // Default
        }

        function updateClock() {
            const clock = document.getElementById('clock_now');
            if (clock) {
                const now = new Date();
                clock.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }

        const activeMethodClasses = ['border-emerald-400', 'bg-emerald-50', 'text-emerald-700', 'ring-2', 'ring-emerald-200'];
        const inactiveMethodClasses = ['border-slate-200', 'bg-slate-50', 'text-slate-600'];

        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-method-item').forEach(el => {
                el.classList.remove(...activeMethodClasses);
                el.classList.add(...inactiveMethodClasses);

                if (el.dataset.method === method) {
                    el.classList.remove(...inactiveMethodClasses);
                    el.classList.add(...activeMethodClasses);
                    el.setAttribute('aria-pressed', 'true');
                } else {
                    el.setAttribute('aria-pressed', 'false');
                }
            });

            els.paymentMethod.value = method;

            const cashGroup = document.getElementById('cashAmountGroup');
            const khqrGroup = document.getElementById('khqrGroup');
            const cardGroup = document.getElementById('cardGroup');
            
            cashGroup.style.display = method === 'cash' ? 'block' : 'none';
            khqrGroup.style.display = method === 'khqr' ? 'block' : 'none';
            cardGroup.style.display = method === 'card' ? 'block' : 'none';

            if (method === 'khqr') generateDynamicKHQR();
            if (method === 'cash') els.modalCashGiven.focus();
        }

        function closePaymentModal() {
            els.paymentModal.style.display = 'none';
            if (window.paymentClock) clearInterval(window.paymentClock);
        }

        function generateDynamicKHQR() {
            const container = document.getElementById('qrcode_container');
            container.innerHTML = '';
            
            const amount = computeSubtotal();
            const details = {
                bakongId: "<?php echo $settings['bank_account'] ?? 'doem_socheat@bkrt'; ?>",
                name: "<?php echo $settings['merchant_name'] ?? 'Doem Socheat'; ?>",
                city: "<?php echo $settings['merchant_city'] ?? 'Phnom Penh'; ?>",
                phone: "<?php echo $settings['phone_number'] ?? '85516367859'; ?>",
                store: "<?php echo $settings['store_label'] ?? 'Mekong CyberUnit'; ?>",
                amount: amount,
                currency: "USD",
                bill: "POS" + Date.now().toString().slice(-8)
            };

            const khqrString = BakongKHQR.generateIndividual(details);
            
            new QRCode(container, {
                text: khqrString,
                width: 220,
                height: 220,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }

        function updateModalChange() {
            const subtotal = computeSubtotal();
            const cash = parseFloat(els.modalCashGiven.value || '0') || 0;
            const change = Math.max(0, cash - subtotal);
            els.modalChange.textContent = money(change);
        }

        function confirmPayment() {
            const method = els.paymentMethod.value;
            const cashGiven = els.modalCashGiven.value;

            if (method === 'cash') {
                const subtotal = computeSubtotal();
                const cash = parseFloat(cashGiven || '0') || 0;
                if (cash < subtotal) {
                    alert('Amount received is less than total payable.');
                    return;
                }
                els.cashGiven.value = cashGiven;
            } else {
                els.cashGiven.value = '';
            }
            els.orderStatus.value = 'completed';

            closePaymentModal();
            els.checkoutForm.submit();
        }

        function syncFormItems() {
            const items = [];
            for (const [id, entry] of cart.entries()) {
                items.push({ product_id: id, quantity: entry.qty });
            }
            
            // Remove existing hidden items
            els.checkoutForm.querySelectorAll('.item-input').forEach(el => el.remove());
            
            items.forEach((item, index) => {
                const idInp = document.createElement('input');
                idInp.type = 'hidden';
                idInp.name = `items[${index}][product_id]`;
                idInp.value = item.product_id;
                idInp.className = 'item-input';
                
                const qtyInp = document.createElement('input');
                qtyInp.type = 'hidden';
                qtyInp.name = `items[${index}][quantity]`;
                qtyInp.value = item.quantity;
                qtyInp.className = 'item-input';
                
                els.checkoutForm.appendChild(idInp);
                els.checkoutForm.appendChild(qtyInp);
            });
        }

        function applyResume() {
            if (!RESUME) return;
            cart.clear();
            for (const it of RESUME.items) {
                const p = PRODUCTS.find(x => x.id === it.product_id);
                if (p) cart.set(it.product_id, { product: p, qty: it.quantity });
            }
            if (RESUME.customer_id) els.checkoutForm.querySelector('#customer').value = RESUME.customer_id;
            renderCart();
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function toggleMenuOrders() {
            const panel = document.getElementById('menuOrdersPanel');
            panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderCategories();
            renderProducts();
            applyResume();
            renderCart();

            els.search.addEventListener('input', renderProducts);
            els.category.addEventListener('change', renderProducts);

            els.btnPay.addEventListener('click', (e) => {
                e.preventDefault();
                showPaymentModal();
            });

            els.terminalOrderStatus.addEventListener('change', (e) => {
                els.orderStatus.value = e.target.value;
            });

            els.modalCashGiven.addEventListener('input', updateModalChange);

            els.search.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    tryQuickAddFirstMatch();
                }
            });

            els.checkoutForm.addEventListener('submit', (e) => {
                if (!cart.size) {
                    e.preventDefault();
                    if (window.POSUI && window.POSUI.toast) {
                        window.POSUI.toast({ type: 'warning', title: '<?php echo __('cart_empty'); ?>', message: '<?php echo __('add_items_msg', ['default' => 'Please add items to your cart before completing the sale.']); ?>' });
                    } else {
                        alert('<?php echo __('cart_empty'); ?>');
                    }
                }
            });
        });
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
