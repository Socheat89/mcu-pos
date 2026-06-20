import React, { useState, useEffect, useRef } from 'react';
import {
  Search,
  ShoppingBag,
  Trash2,
  ArrowRight,
  ArrowLeft,
  Clock,
  CreditCard,
  QrCode,
  Wallet,
  X,
  ChevronDown,
  Check,
  Plus,
  Minus,
  Sparkles,
  Moon,
  Sun,
  Activity,
  Layers,
  TrendingUp,
  BarChart2,
  AlertTriangle,
  Package,
  UserCircle,
  Receipt,
  Zap,
  ChevronRight,
  Languages,
  LogOut,
} from 'lucide-react';
import {
  ResponsiveContainer,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  AreaChart,
  Area
} from 'recharts';
import confetti from 'canvas-confetti';

// ─── Translations Dictionary ──────────────────────────────────
const translations = {
  en: {
    exit: "Dashboard",
    sell: "Sell",
    reports: "Reports",
    pending: "Hold",
    all: "All",
    cart: "Cart",
    items: "items",
    customer: "Customer",
    walk_in: "Walk-in (General Customer)",
    mode: "Mode",
    mode_sell: "Sell",
    mode_hold: "Hold",
    receipt: "Receipt",
    auto_print: "Auto-Print",
    subtotal: "Subtotal",
    tax: "Tax (0%)",
    total: "Total",
    clear_cart_confirm: "Clear all items in cart?",
    clear_cart: "Clear Cart",
    checkout: "Checkout",
    hold_order: "Hold",
    pending_orders_title: "Pending Orders",
    no_pending_orders: "No pending orders",
    order: "Order",
    note: "Note",
    resume: "Resume",
    payment_title: "Payment",
    payment_subtitle: "Checkout Processing",
    total_payable: "Total Payable",
    payment_method: "Payment Method",
    cash: "Cash",
    khqr: "KHQR",
    card: "Card",
    cash_received: "Cash Received",
    change: "Change",
    waiting_khqr: "Waiting for Bakong payment...",
    connecting_card: "Connecting to card reader...",
    insert_card: "Insert card into POS reader device",
    submit_handshake: "Submit to initialize handshake",
    confirm_finish: "Confirm & Finish",
    cancel: "Cancel",
    search_placeholder: "Search products — barcode, SKU, name...",
    out_of_stock: "Out of stock",
    low_stock: "left",
    toast_added: "Added",
    toast_added_msg: "has been added to cart.",
    toast_no_stock: "Out of Stock",
    toast_no_stock_msg: "is out of stock.",
    toast_limit_stock: "Stock Limit Reached",
    toast_limit_stock_msg: "Only :qty units left in stock.",
    toast_clear: "Cleared",
    toast_clear_msg: "Cart has been cleared.",
    toast_recovered: "Order Restored",
    toast_recovered_msg: "Continuing order #:id",
    toast_insufficient_cash: "Insufficient Cash",
    toast_insufficient_cash_msg: "Received amount is less than total.",
    toast_checkout_success: "Success!",
    toast_checkout_success_msg: "Processing...",
    sales_by_category: "Sales by Category (USD)",
    current_stock_levels: "Current Stock",
    report_title: "Sales & Stock Report",
    close_report: "Close Reports",
    no_products: "No products found",
    no_products_subtitle: "No products match your search",
    products_label: "Products",
    stock_alerts: "Stock alerts",
    out: "out",
  },
  km: {
    exit: "ទៅកាន់ Dashboard",
    sell: "លក់ទំនិញ",
    reports: "របាយការណ៍",
    pending: "រង់ចាំ",
    all: "ទាំងអស់",
    cart: "កន្ត្រក",
    items: "មុខទំនិញ",
    customer: "អតិថិជន",
    walk_in: "Walk-in (អតិថិជនទូទៅ)",
    mode: "របៀបលក់",
    mode_sell: "លក់ Sell",
    mode_hold: "រង់ចាំ Hold",
    receipt: "វិក្កយបត្រ",
    auto_print: "បោះពុម្ពស្វ័យប្រវត្តិ",
    subtotal: "សរុបរង",
    tax: "ពន្ធ (0%)",
    total: "សរុប Total",
    clear_cart_confirm: "លុបទំនិញទាំងអស់ក្នុងកន្ត្រក?",
    clear_cart: "សម្អាតកន្ត្រក",
    checkout: "ទូទាត់ Checkout",
    hold_order: "ដាក់រង់ចាំ Hold",
    pending_orders_title: "បញ្ជាទិញរង់ចាំ",
    no_pending_orders: "មិនមាន order រង់ចាំទេ",
    order: "ការបញ្ជាទិញ",
    note: "កំណត់ចំណាំ",
    resume: "បន្ត",
    payment_title: "ទូទាត់ប្រាក់",
    payment_subtitle: "Checkout Processing",
    total_payable: "ទឹកប្រាក់សរុប",
    payment_method: "វិធីបង់ប្រាក់",
    cash: "សាច់ប្រាក់",
    khqr: "KHQR",
    card: "កាត Card",
    cash_received: "ប្រាក់ទទួលបាន",
    change: "ប្រាក់អាប់ Change",
    waiting_khqr: "កំពុងរង់ចាំការផ្ទេរប្រាក់ Bakong...",
    connecting_card: "កំពុងភ្ជាប់ទៅឧបករណ៍កាត...",
    insert_card: "បញ្ចូលកាត POS reader device",
    submit_handshake: "Submit to initialize handshake",
    confirm_finish: "បញ្ជាក់ និង បញ្ចប់ Confirm",
    cancel: "បោះបង់ Cancel",
    search_placeholder: "ស្វែងរកទំនិញ — barcode, SKU, ឈ្មោះ...",
    out_of_stock: "អស់ស្តុក",
    low_stock: "នៅសល់",
    toast_added: "បានបន្ថែម",
    toast_added_msg: "បានបញ្ចូលក្នុងកន្ត្រក។",
    toast_no_stock: "អស់ស្តុក",
    toast_no_stock_msg: "មិនមានក្នុងស្តុកទេ។",
    toast_limit_stock: "ដល់កម្រិតស្តុក",
    toast_limit_stock_msg: "មានតែ :qty គ្រាប់នៅសល់។",
    toast_clear: "បានលុប",
    toast_clear_msg: "កន្ត្រកទទេហើយ។",
    toast_recovered: "បានស្ដារ Order",
    toast_recovered_msg: "កំពុងបន្ត order #:id",
    toast_insufficient_cash: "ប្រាក់មិនគ្រប់",
    toast_insufficient_cash_msg: "ចំនួនទឹកប្រាក់តិចជាងសរុប।",
    toast_checkout_success: "ជោគជ័យ!",
    toast_checkout_success_msg: "កំពុងដំណើរការ...",
    sales_by_category: "ការលក់តាមប្រភេទ (USD)",
    current_stock_levels: "ស្តុកបច្ចុប្បន្ន",
    report_title: "របាយការណ៍លក់ និងស្តុក",
    close_report: "បិទរបាយការណ៍",
    no_products: "រកមិនឃើញទំនិញទេ",
    no_products_subtitle: "No products match your search",
    products_label: "ទំនិញ",
    stock_alerts: "ស្តុកតិច/អស់",
    out: "អស់",
  },
  zh: {
    exit: "返回控制台",
    sell: "销售",
    reports: "报告",
    pending: "挂单",
    all: "全部",
    cart: "购物车",
    items: "件商品",
    customer: "顾客",
    walk_in: "散客 (普通顾客)",
    mode: "模式",
    mode_sell: "销售",
    mode_hold: "挂单",
    receipt: "小票",
    auto_print: "自动打印",
    subtotal: "小计",
    tax: "税 (0%)",
    total: "总计",
    clear_cart_confirm: "确认清空购物车吗？",
    clear_cart: "清空",
    checkout: "结账",
    hold_order: "挂单",
    pending_orders_title: "挂单列表",
    no_pending_orders: "暂无挂单",
    order: "订单",
    note: "备注",
    resume: "继续",
    payment_title: "付款",
    payment_subtitle: "结账处理",
    total_payable: "应付总额",
    payment_method: "付款方式",
    cash: "现金",
    khqr: "KHQR",
    card: "刷卡",
    cash_received: "实收金额",
    change: "找零",
    waiting_khqr: "等待巴孔扫码支付...",
    connecting_card: "正在连接刷卡机...",
    insert_card: "请将卡插入POS机",
    submit_handshake: "确认以初始化握手",
    confirm_finish: "确认并结束",
    cancel: "取消",
    search_placeholder: "搜索商品 — 条码、SKU、名称...",
    out_of_stock: "无库存",
    low_stock: "剩余",
    toast_added: "已添加",
    toast_added_msg: "已加入购物车。",
    toast_no_stock: "无库存",
    toast_no_stock_msg: "该商品无库存。",
    toast_limit_stock: "达到库存上限",
    toast_limit_stock_msg: "库存仅剩 :qty 件。",
    toast_clear: "已清空",
    toast_clear_msg: "购物车已清空。",
    toast_recovered: "订单已恢复",
    toast_recovered_msg: "正在继续订单 #:id",
    toast_insufficient_cash: "金额不足",
    toast_insufficient_cash_msg: "实收金额小于应付总额。",
    toast_checkout_success: "成功！",
    toast_checkout_success_msg: "正在处理...",
    sales_by_category: "按类别销售额 (USD)",
    current_stock_levels: "当前库存水平",
    report_title: "销售与库存报告",
    close_report: "关闭报告",
    no_products: "未找到商品",
    no_products_subtitle: "没有找到符合搜索条件的商品",
    products_label: "商品",
    stock_alerts: "库存提醒",
    out: "售罄",
  }
};

const languages = [
  { code: 'km', label: 'ភាសាខ្មែរ', flag: '🇰🇭' },
  { code: 'en', label: 'English', flag: '🇺🇸' },
  { code: 'zh', label: '中文', flag: '🇨🇳' }
];

// ─── Bakong KHQR Generator (NBC Standard) ──────────────────────
const crcTable = [
  0x0000, 0x1021, 0x2042, 0x3063, 0x4084, 0x50A5, 0x60C6, 0x70E7,
  0x8108, 0x9129, 0xA14A, 0xB16B, 0xC18C, 0xD1AD, 0xE1CE, 0xF1EF,
  0x1231, 0x0210, 0x3273, 0x2252, 0x52B5, 0x4294, 0x72F7, 0x62D6,
  0x9339, 0x8318, 0xB37B, 0xA35A, 0xD3BD, 0xC39C, 0xF3FF, 0xE3DE,
  0x2462, 0x3443, 0x0420, 0x1401, 0x64E6, 0x74C7, 0x44A4, 0x5485,
  0xA56A, 0xB54B, 0x8528, 0x9509, 0xE5EE, 0xF5CF, 0xC5AC, 0xD58D,
  0x3653, 0x2672, 0x1611, 0x0630, 0x76D7, 0x66F6, 0x5695, 0x46B4,
  0xB75B, 0xA77A, 0x9719, 0x8738, 0xF7DF, 0xE7FE, 0xD79D, 0xC7BC,
  0x48C4, 0x58E5, 0x6886, 0x78A7, 0x0840, 0x1861, 0x2802, 0x3823,
  0xC9CC, 0xD9ED, 0xE98E, 0xF9AF, 0x8948, 0x9969, 0xA90A, 0xB92B,
  0x5AF5, 0x4AD4, 0x7AB7, 0x6A96, 0x1A71, 0x0A50, 0x3A33, 0x2A12,
  0xDBFD, 0xCBDC, 0xFBBF, 0xEB9E, 0x9B79, 0x8B58, 0xBB3B, 0xAB1A,
  0x6CA6, 0x7C87, 0x4CE4, 0x5CC5, 0x2C22, 0x3C03, 0x0C60, 0x1C41,
  0xEDAE, 0xFD8F, 0xCDEC, 0xDDCD, 0xAD2A, 0xBD0B, 0x8D68, 0x9D49,
  0x7E97, 0x6EB6, 0x5ED5, 0x4EF4, 0x3E13, 0x2E32, 0x1E51, 0x0E70,
  0xFF9F, 0xEFBE, 0xDFDD, 0xCFFC, 0xBF1B, 0xAF3A, 0x9F59, 0x8F78,
  0x9188, 0x81A9, 0xB1CA, 0xA1EB, 0xD10C, 0xC12D, 0xF14E, 0xE16F,
  0x1080, 0x00A1, 0x30C2, 0x20E3, 0x5004, 0x4025, 0x7046, 0x6067,
  0x83B9, 0x9398, 0xA3FB, 0xB3DA, 0xC33D, 0xD31C, 0xE37F, 0xF35E,
  0x02B1, 0x1290, 0x22F3, 0x32D2, 0x4235, 0x5214, 0x6277, 0x7256,
  0xB5EA, 0xA5CB, 0x95A8, 0x8589, 0xF56E, 0xE54F, 0xD52C, 0xC50D,
  0x34E2, 0x24C3, 0x14A0, 0x0481, 0x7466, 0x6447, 0x5424, 0x4405,
  0xA7DB, 0xB7FA, 0x8799, 0x97B8, 0xE75F, 0xF77E, 0xC71D, 0xD73C,
  0x26D3, 0x36F2, 0x0691, 0x16B0, 0x6657, 0x7676, 0x4615, 0x5634,
  0xD94C, 0xC96D, 0xF90E, 0xE92F, 0x99C8, 0x89E9, 0xB98A, 0xA9AB,
  0x5844, 0x4865, 0x7806, 0x6827, 0x18C0, 0x08E1, 0x3882, 0x28A3,
  0xCB7D, 0xDB5C, 0xEB3F, 0xFB1E, 0x8BF9, 0x9BD8, 0xABBB, 0xBB9A,
  0x4A75, 0x5A54, 0x6A37, 0x7A16, 0x0AF1, 0x1AD0, 0x2AB3, 0x3A92,
  0xFD2E, 0xED0F, 0xDD6C, 0xCD4D, 0xBDAA, 0xAD8B, 0x9DE8, 0x8DC9,
  0x7C26, 0x6C07, 0x5C64, 0x4C45, 0x3CA2, 0x2C83, 0x1CE0, 0x0CC1,
  0xEF1F, 0xFF3E, 0xCF5D, 0xDF7C, 0xAF9B, 0xBFBA, 0x8FD9, 0x9FF8,
  0x6E17, 0x7E36, 0x4E55, 0x5E74, 0x2E93, 0x3EB2, 0x0ED1, 0x1EF0,
];

function calculateCRC(str) {
  let crc = 0xffff;
  const bytes = new TextEncoder().encode(str);
  for (let i = 0; i < bytes.length; i++) {
    const index = (bytes[i] ^ (crc >> 8)) & 0xff;
    crc = crcTable[index] ^ (crc << 8);
  }
  return (crc & 0xffff).toString(16).toUpperCase().padStart(4, '0');
}

function formatTag(tag, value) {
  if (value === null || value === undefined || value === '') return '';
  const valStr = String(value);
  return tag.toString().padStart(2, '0') + valStr.length.toString().padStart(2, '0') + valStr;
}

function generateKHQRString(data) {
  let str = '';
  str += formatTag('00', '01');
  str += formatTag('01', '11');
  let accountInfo = '';
  accountInfo += formatTag('00', data.bakongId);
  str += formatTag('29', accountInfo);
  str += formatTag('52', '5999');
  str += formatTag('53', data.currency === 'KHR' ? '116' : '840');
  if (data.amount > 0) {
    const amountStr = data.currency === 'KHR'
      ? Math.round(data.amount).toString()
      : (data.amount % 1 === 0 ? data.amount.toString() : data.amount.toFixed(2));
    str += formatTag('54', amountStr);
  }
  str += formatTag('58', 'KH');
  str += formatTag('59', data.name || 'Merchant');
  str += formatTag('60', data.city || 'Phnom Penh');
  let addData = '';
  if (data.bill) addData += formatTag('01', data.bill);
  if (data.phone) addData += formatTag('02', data.phone);
  if (data.store) addData += formatTag('03', data.store);
  if (data.terminal) addData += formatTag('07', data.terminal);
  if (addData) str += formatTag('62', addData);
  str += '6304';
  str += calculateCRC(str);
  return str;
}

// ─── Main App Component ────────────────────────────────────────
export default function App() {
  // Load initial data from window
  const initialProducts = window.PRODUCTS || [
    { id: 1, name: 'Espresso Single', price: 1.5, stock: 25, category: 'Coffee', image: '' },
    { id: 2, name: 'Ice Latte Premium', price: 2.2, stock: 4, category: 'Coffee', image: '' },
    { id: 3, name: 'Croissant Butter', price: 1.8, stock: 12, category: 'Bakery', image: '' },
    { id: 4, name: 'Matcha Green Tea', price: 2.5, stock: 0, category: 'Tea', image: '' },
    { id: 5, name: 'Club Sandwich XL', price: 3.5, stock: 15, category: 'Food', image: '' },
  ];
  const initialCustomers = window.CUSTOMERS || [
    { id: 101, name: 'Sok Mean', phone: '012345678' },
    { id: 102, name: 'Dara Roth', phone: '098765432' }
  ];
  const initialSettings = window.SETTINGS || {
    bank_account: 'doem_socheat@bkrt',
    merchant_name: 'Doem Socheat',
    merchant_city: 'Phnom Penh',
    phone_number: '85516367859',
    store_label: 'Mekong CyberUnit',
    pos_method_cash_enabled: '1',
    pos_method_khqr_enabled: '1',
    pos_method_card_enabled: '1'
  };
  const initialPendingOrders = window.PENDING_ORDERS || [];
  const initialResumeOrder = window.RESUME || null;

  // State
  const [products] = useState(initialProducts);
  const [customers] = useState(initialCustomers);
  const [settings] = useState(initialSettings);
  const [pendingOrders] = useState(initialPendingOrders);
  const [resumeOrder] = useState(initialResumeOrder);

  const [cart, setCart] = useState([]);
  const [selectedCustomerId, setSelectedCustomerId] = useState('');
  const [orderStatus, setOrderStatus] = useState('completed');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [darkMode, setDarkMode] = useState(false); // Light-first

  const [paymentModalOpen, setPaymentModalOpen] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [cashGiven, setCashGiven] = useState('');
  const [cardSimulating, setCardSimulating] = useState(false);
  const [cardProgress, setCardProgress] = useState(0);
  const [pendingOrdersOpen, setPendingOrdersOpen] = useState(false);
  const [analyticsViewOpen, setAnalyticsViewOpen] = useState(false);
  const [toast, setToast] = useState(null);

  const [timeStr, setTimeStr] = useState(new Date().toLocaleTimeString());
  const formRef = useRef(null);

  // Translation State & Dropdown Handlers
  const [currentLang, setCurrentLang] = useState(window.CURRENT_LANG || 'km');
  const [langMenuOpen, setLangMenuOpen] = useState(false);
  const langMenuRef = useRef(null);

  // Translation helper
  const t = (key, fallback) => {
    return translations[currentLang]?.[key] || fallback;
  };

  // Close dropdown on click outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (langMenuRef.current && !langMenuRef.current.contains(event.target)) {
        setLangMenuOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const changeLang = (code) => {
    setCurrentLang(code);
    setLangMenuOpen(false);
    // Persist language setting in PHP backend session/cookie
    fetch(`${window.BASE_PATH || ''}/public/set_lang.php?lang=${code}`)
      .catch(err => console.error("Error setting language session:", err));
  };

  // Sync dark mode class
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [darkMode]);

  // Clock
  useEffect(() => {
    const timer = setInterval(() => {
      setTimeStr(new Date().toLocaleTimeString());
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // Handle Resume Order
  useEffect(() => {
    if (resumeOrder && resumeOrder.items) {
      const restoredCart = [];
      resumeOrder.items.forEach(item => {
        const prod = products.find(p => p.id === item.product_id);
        if (prod) {
          restoredCart.push({ product: prod, quantity: item.quantity });
        }
      });
      setCart(restoredCart);
      if (resumeOrder.customer_id) {
        setSelectedCustomerId(resumeOrder.customer_id.toString());
      }
      showToast('info', t('toast_recovered', 'បានស្ដារ Order'), t('toast_recovered_msg', 'កំពុងបន្ត order #:id').replace(':id', resumeOrder.id));
    }
  }, [resumeOrder, products]);

  // Toast
  const showToast = (type, title, message) => {
    setToast({ type, title, message });
    setTimeout(() => setToast(null), 4000);
  };

  // ─── Cart Operations ──────────────────────────────────────
  const addToCart = (product) => {
    if (product.stock <= 0) {
      showToast('warning', t('toast_no_stock', 'អស់ស្តុក'), `${product.name} ${t('toast_no_stock_msg', 'មិនមានក្នុងស្តុកទេ។')}`);
      return;
    }
    const existing = cart.find(item => item.product.id === product.id);
    if (existing) {
      if (existing.quantity >= product.stock) {
        showToast('warning', t('toast_limit_stock', 'ដល់កម្រិតស្តុក'), t('toast_limit_stock_msg', 'មានតែ :qty ​គ្រាប់នៅសល់។').replace(':qty', product.stock));
        return;
      }
      setCart(cart.map(item =>
        item.product.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
      ));
    } else {
      setCart([...cart, { product, quantity: 1 }]);
    }
  };

  const updateCartQty = (productId, delta) => {
    const existing = cart.find(item => item.product.id === productId);
    if (!existing) return;
    const nextQty = existing.quantity + delta;
    if (nextQty <= 0) {
      setCart(cart.filter(item => item.product.id !== productId));
    } else {
      if (nextQty > existing.product.stock) {
        showToast('warning', t('toast_limit_stock', 'ដល់កម្រិតស្តុក'), t('toast_limit_stock_msg', 'មានតែ :qty ​គ្រាប់នៅសល់។').replace(':qty', existing.product.stock));
        return;
      }
      setCart(cart.map(item =>
        item.product.id === productId ? { ...item, quantity: nextQty } : item
      ));
    }
  };

  const clearCart = () => {
    if (cart.length === 0) return;
    if (window.confirm(t('clear_cart_confirm', 'លុបទំនិញទាំងអស់ក្នុងកន្ត្រក?'))) {
      setCart([]);
      showToast('info', t('toast_clear', 'បានលុប'), t('toast_clear_msg', 'កន្ត្រកទទេហើយ។'));
    }
  };

  const handleQuickAdd = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const filtered = getFilteredProducts();
      if (filtered.length > 0) {
        const match = filtered[0];
        if (match.stock > 0) {
          addToCart(match);
          setSearchQuery('');
          showToast('success', t('toast_added', 'បានបន្ថែម'), `${match.name} ${t('toast_added_msg', 'បានបញ្ចូលក្នុងកន្ត្រក។')}`);
        }
      }
    }
  };

  // ─── Helpers ──────────────────────────────────────────────
  const getSubtotal = () => cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
  const getGrandTotal = () => getSubtotal();
  const getCategories = () => ['All', ...new Set(products.map(p => p.category))];

  const getFilteredProducts = () => {
    const q = searchQuery.toLowerCase().trim();
    return products.filter(p => {
      const matchesCat = !selectedCategory || selectedCategory === 'All' || p.category === selectedCategory;
      if (!matchesCat) return false;
      if (!q) return true;
      return (
        p.name.toLowerCase().includes(q) ||
        (p.sku && p.sku.toLowerCase().includes(q)) ||
        (p.barcode && p.barcode.toLowerCase().includes(q))
      );
    });
  };

  // Card Terminal simulator
  const startCardSimulation = () => {
    setCardSimulating(true);
    setCardProgress(0);
    const interval = setInterval(() => {
      setCardProgress(prev => {
        if (prev >= 100) {
          clearInterval(interval);
          setTimeout(() => {
            setCardSimulating(false);
            submitCheckout();
          }, 600);
          return 100;
        }
        return prev + 20;
      });
    }, 400);
  };

  // Submit Checkout
  const handleCheckoutSubmit = () => {
    if (cart.length === 0) return;
    if (orderStatus === 'pending') {
      submitCheckout();
      return;
    }
    if (paymentMethod === 'cash') {
      const total = getGrandTotal();
      const cashVal = parseFloat(cashGiven) || 0;
      if (cashVal < total) {
        showToast('error', t('toast_insufficient_cash', 'ប្រាក់មិនគ្រប់'), t('toast_insufficient_cash_msg', 'ចំនួនទឹកប្រាក់តិចជាងសរុប។'));
        return;
      }
      submitCheckout();
    } else if (paymentMethod === 'card') {
      startCardSimulation();
    } else if (paymentMethod === 'khqr') {
      submitCheckout();
    }
  };

  const submitCheckout = () => {
    confetti({
      particleCount: 150,
      spread: 80,
      origin: { y: 0.6 }
    });
    showToast('success', t('toast_checkout_success', 'ជោគជ័យ!'), t('toast_checkout_success_msg', 'កំពុងដំណើរការ...'));
    setTimeout(() => {
      if (formRef.current) {
        formRef.current.submit();
      }
    }, 1200);
  };

  // KHQR
  const getKHQRString = () => {
    const total = getGrandTotal();
    return generateKHQRString({
      bakongId: settings.bank_account || 'doem_socheat@bkrt',
      name: settings.merchant_name || 'Doem Socheat',
      city: settings.merchant_city || 'Phnom Penh',
      phone: settings.phone_number || '85516367859',
      store: settings.store_label || 'Mekong CyberUnit',
      amount: total,
      currency: 'USD',
      bill: 'POS' + Date.now().toString().slice(-8)
    });
  };

  // Analytics data
  const getCategorySalesData = () => {
    const map = {};
    products.forEach(p => {
      map[p.category] = (map[p.category] || 0) + (p.price * (Math.floor(Math.random() * 20) + 5));
    });
    return Object.keys(map).map(cat => ({ name: cat, sales: parseFloat(map[cat].toFixed(2)) }));
  };

  const getStockLevelsData = () => {
    return products.slice(0, 8).map(p => ({ name: p.name.substring(0, 10), stock: p.stock }));
  };

  const cartItemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
  const visibleProductCount = getFilteredProducts().length;
  const lowStockCount = products.filter(p => p.stock > 0 && p.stock <= 5).length;
  const outOfStockCount = products.filter(p => p.stock <= 0).length;
  const selectedCustomer = customers.find(c => String(c.id) === String(selectedCustomerId));
  const quickTenderOptions = Array.from(new Set([
    getGrandTotal(),
    Math.ceil(getGrandTotal()),
    Math.ceil(getGrandTotal() / 5) * 5,
    Math.ceil(getGrandTotal() / 10) * 10
  ]
    .filter(amount => amount > 0 && amount >= getGrandTotal())
    .map(amount => Number(amount.toFixed(2)))
  )).slice(0, 4);

  // ═══════════════════════════════════════════════════════════
  // RENDER
  // ═══════════════════════════════════════════════════════════
  return (
    <div className={`h-screen flex flex-col overflow-hidden transition-colors duration-300 ${darkMode ? 'bg-brand-bgDark text-brand-textDark' : 'bg-brand-bgLight text-brand-textLight'}`}>

      {/* Hidden PHP checkout form */}
      <form ref={formRef} id="checkoutForm" method="POST" action={`${window.BASE_PATH || ''}/${window.SUBDOMAIN || ''}/pos/orders/create`} style={{ display: 'none' }}>
        <input type="hidden" name="order_status" value={orderStatus} />
        <input type="hidden" name="payment_method" value={paymentMethod} />
        <input type="hidden" name="cash_given" value={cashGiven} />
        <input type="hidden" name="customer_id" value={selectedCustomerId || ""} />
        {resumeOrder && <input type="hidden" name="resume_order_id" value={resumeOrder.id} />}
        {cart.map((item, index) => (
          <React.Fragment key={item.product.id}>
            <input type="hidden" name={`items[${index}][product_id]`} value={item.product.id} />
            <input type="hidden" name={`items[${index}][quantity]`} value={item.quantity} />
          </React.Fragment>
        ))}
      </form>

      {/* ─── Toast ─── */}
      {toast && (
        <div className="fixed right-5 top-5 z-[100] animate-slide-up">
          <div className={`flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-glass-lg backdrop-blur-xl border ${
            toast.type === 'success' ? 'bg-brand-success/90 border-brand-success/50 text-white' :
            toast.type === 'warning' ? 'bg-brand-warning/90 border-brand-warning/50 text-white' :
            toast.type === 'error' ? 'bg-brand-danger/90 border-brand-danger/50 text-white' :
            'bg-brand-cyan/90 border-brand-cyan/50 text-white'
          }`}>
            <Sparkles className="h-4 w-4 flex-shrink-0" />
            <div>
              <div className="font-bold text-sm leading-tight">{toast.title}</div>
              <div className="text-xs opacity-90 mt-0.5">{toast.message}</div>
            </div>
          </div>
        </div>
      )}

      {/* ─── Header ─── */}
      <header className="flex-shrink-0">
        <div className="bg-[#714B67] px-4 py-2.5 sm:px-6 flex flex-wrap items-center justify-between gap-3 text-white border-b border-[#5b3c53] shadow-sm">
          {/* Left: Branding */}
          <div className="flex min-w-0 items-center gap-3">
            <div className="h-9 w-9 flex-shrink-0 rounded-lg bg-white/10 flex items-center justify-center">
              <Layers className="h-4.5 w-4.5 text-white" />
            </div>
            <div className="min-w-0">
              <div className="max-w-[180px] truncate text-[9px] font-bold uppercase tracking-[0.15em] text-[#d8c2d4] sm:max-w-none">{settings.store_label}</div>
              <div className="flex items-center gap-2">
                <h1 className="text-sm font-black tracking-tight text-white leading-tight">Mekong POS</h1>
                <span className="hidden xs:inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[8px] font-bold bg-white/10 text-[#a3ecd1] border border-white/10">
                  <span className="h-1 w-1 rounded-full bg-emerald-400 animate-ping"></span>
                  Terminal Live
                </span>
              </div>
            </div>
          </div>

          {/* Right: Controls */}
          <div className="flex flex-wrap items-center justify-end gap-2">
            {/* Exit/Dashboard Button */}
            <a
              href={window.DASHBOARD_URL || `${window.BASE_PATH || ''}/${window.SUBDOMAIN || ''}/pos/dashboard`}
              className="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-[11px] font-bold border border-white/20 hover:bg-white/10 text-white transition-all"
            >
              <ArrowLeft className="h-3.5 w-3.5" />
              <span>{t('exit', 'Dashboard')}</span>
            </a>

            {/* Analytics toggle */}
            <button
              onClick={() => setAnalyticsViewOpen(!analyticsViewOpen)}
              className={`flex items-center gap-1.5 rounded-md px-3 py-1.5 text-[11px] font-bold transition-all duration-300 ${
                analyticsViewOpen
                  ? 'bg-[#00A09D] text-white border border-[#008d8a]'
                  : 'border border-white/20 hover:bg-white/10 text-white'
              }`}
            >
              <BarChart2 className="h-3.5 w-3.5" />
              <span className="hidden sm:inline">{analyticsViewOpen ? t('sell', 'លក់ទំនិញ') : t('reports', 'របាយការណ៍')}</span>
            </button>

            {/* Pending orders */}
            <button
              onClick={() => setPendingOrdersOpen(true)}
              className="relative flex items-center gap-1.5 rounded-md px-3 py-1.5 text-[11px] font-bold border border-white/20 hover:bg-white/10 text-white transition-all"
            >
              <Clock className="h-3.5 w-3.5 text-[#a3e5ec]" />
              <span className="hidden sm:inline">{t('pending', 'រង់ចាំ')}</span>
              {pendingOrders.length > 0 && (
                <span className="absolute -top-1.5 -right-1.5 bg-[#e05038] text-white rounded-full text-[9px] font-bold px-1.5 py-0.5">
                  {pendingOrders.length}
                </span>
              )}
            </button>

            {/* Language Switcher Dropdown */}
            <div className="relative" ref={langMenuRef}>
              <button
                onClick={() => setLangMenuOpen(!langMenuOpen)}
                className="h-8 px-2.5 rounded-md flex items-center gap-1.5 transition-all text-[11px] font-bold border border-white/20 hover:bg-white/10 text-white bg-transparent"
              >
                <Languages className="h-3.5 w-3.5 text-[#a3e5ec]" />
                <span className="uppercase">{currentLang === 'en' ? 'EN' : currentLang === 'km' ? 'KH' : 'ZH'}</span>
                <ChevronDown className="h-3 w-3 text-[#d8c2d4]" />
              </button>

              {langMenuOpen && (
                <div className="absolute right-0 mt-1.5 w-36 rounded-lg shadow-md border p-1 z-50 bg-white border-gray-200 text-brand-textLight">
                  {languages.map(lang => (
                    <button
                      key={lang.code}
                      onClick={() => changeLang(lang.code)}
                      className={`w-full flex items-center justify-between rounded-md px-2.5 py-1.5 text-[11px] font-bold transition-all ${
                        currentLang === lang.code
                          ? 'bg-[#714B67]/10 text-[#714B67] border border-[#714B67]/20'
                          : 'hover:bg-gray-100 text-gray-600 border border-transparent'
                      }`}
                    >
                      <span className="flex items-center gap-1.5">
                        <span>{lang.flag}</span>
                        <span>{lang.label}</span>
                      </span>
                      {currentLang === lang.code && <Check className="h-3 w-3 text-[#714B67]" />}
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Dark mode hidden or styled neutrally */}
            <button
              onClick={() => setDarkMode(!darkMode)}
              className="h-8 w-8 rounded-md flex items-center justify-center transition-all border border-white/20 hover:bg-white/10 text-white"
            >
              {darkMode ? <Sun className="h-3.5 w-3.5 text-amber-300" /> : <Moon className="h-3.5 w-3.5 text-slate-300" />}
            </button>

            {/* Clock */}
            <div className="hidden md:flex items-center gap-1.5 rounded-md px-3 py-1.5 text-[11px] font-mono font-bold tracking-wider border border-white/20 bg-white/5 text-white">
              <Activity className="h-3 w-3 text-[#a3e5ec]" />
              <span>{timeStr}</span>
            </div>
          </div>
        </div>
      </header>

      {/* ─── Main Content Area ─── */}
      <div className="flex-1 min-h-0 flex flex-col overflow-hidden lg:flex-row">

        {analyticsViewOpen ? (
          /* ═══ Analytics View ═══ */
          <div className="flex-1 overflow-y-auto p-3 sm:p-5 animate-fade-in">
            <div className="mx-auto max-w-7xl space-y-4">
              <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-2">
                  <TrendingUp className="h-5 w-5 text-brand-cyan" />
                  <h2 className="text-base font-extrabold tracking-tight">{t('report_title', 'របាយការណ៍លក់ និងស្តុក')}</h2>
                </div>
                <button
                  onClick={() => setAnalyticsViewOpen(false)}
                  className="text-[10px] font-bold uppercase tracking-wider text-brand-muted hover:text-brand-cyan transition"
                >
                  {t('close_report', 'បិទ')} ✕
                </button>
              </div>

              <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {[
                  { label: t('cart', 'Cart'), value: `$${getGrandTotal().toFixed(2)}`, icon: Receipt },
                  { label: t('items', 'items'), value: cartItemCount, icon: ShoppingBag },
                  { label: t('stock_alerts', 'Stock alerts'), value: `${lowStockCount}/${outOfStockCount}`, icon: AlertTriangle },
                  { label: t('pending', 'Hold'), value: pendingOrders.length, icon: Clock }
                ].map((stat) => {
                  const StatIcon = stat.icon;
                  return (
                    <div
                      key={stat.label}
                      className={`rounded-lg border p-3 ${darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'} shadow-card`}
                    >
                      <div className="flex items-center justify-between gap-3">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-brand-muted">{stat.label}</span>
                        <StatIcon className="h-4 w-4 text-brand-cyan" />
                      </div>
                      <div className="mt-2 text-lg font-black tracking-tight">{stat.value}</div>
                    </div>
                  );
                })}
              </div>

              <div className="grid gap-4 md:grid-cols-3">
                {/* Category Sales */}
                <div className={`md:col-span-2 p-4 sm:p-5 rounded-lg ${darkMode ? 'bg-brand-surfDark border border-white/5' : 'bg-white border border-gray-200'} shadow-card`}>
                  <h3 className="text-[10px] font-bold text-brand-muted mb-4 uppercase tracking-wider">{t('sales_by_category', 'ការលក់តាមប្រភេទ (USD)')}</h3>
                  <div className="h-64">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={getCategorySalesData()}>
                        <XAxis dataKey="name" stroke="#64748B" fontSize={11} tickLine={false} axisLine={false} />
                        <YAxis stroke="#64748B" fontSize={11} tickLine={false} axisLine={false} />
                        <Tooltip
                          contentStyle={{
                            background: darkMode ? 'rgba(36, 50, 45, 0.96)' : 'rgba(255, 250, 242, 0.96)',
                            border: darkMode ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(0,0,0,0.1)',
                            borderRadius: '12px',
                            color: darkMode ? '#E9E3D8' : '#1B1A17',
                            backdropFilter: 'blur(8px)'
                          }}
                        />
                        <Bar dataKey="sales" fill="url(#cyanVioletGrad)" radius={[6, 6, 0, 0]}>
                          <defs>
                            <linearGradient id="cyanVioletGrad" x1="0" y1="0" x2="0" y2="1">
                              <stop offset="0%" stopColor="#0F766E" />
                              <stop offset="100%" stopColor="#E76F51" />
                            </linearGradient>
                          </defs>
                        </Bar>
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </div>

                {/* Stock Levels */}
                <div className={`p-4 sm:p-5 rounded-lg ${darkMode ? 'bg-brand-surfDark border border-white/5' : 'bg-white border border-gray-200'} shadow-card`}>
                  <h3 className="text-[10px] font-bold text-brand-muted mb-4 uppercase tracking-wider">{t('current_stock_levels', 'ស្តុកបច្ចុប្បន្ន')}</h3>
                  <div className="h-64">
                    <ResponsiveContainer width="100%" height="100%">
                      <AreaChart data={getStockLevelsData()}>
                        <defs>
                          <linearGradient id="cyanAreaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stopColor="#0F766E" stopOpacity={0.4} />
                            <stop offset="100%" stopColor="#0F766E" stopOpacity={0.0} />
                          </linearGradient>
                        </defs>
                        <XAxis dataKey="name" stroke="#64748B" fontSize={9} tickLine={false} />
                        <Tooltip
                          contentStyle={{
                            background: darkMode ? 'rgba(36, 50, 45, 0.96)' : 'rgba(255, 250, 242, 0.96)',
                            border: darkMode ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(0,0,0,0.1)',
                            borderRadius: '12px',
                            color: darkMode ? '#E9E3D8' : '#1B1A17'
                          }}
                        />
                        <Area type="monotone" dataKey="stock" stroke="#0F766E" fill="url(#cyanAreaGrad)" strokeWidth={2} />
                      </AreaChart>
                    </ResponsiveContainer>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ) : (
          /* ═══ POS Terminal View ═══ */
          <>
            {/* ─── Left: Cart Sidebar ─── */}
            <aside className={`h-[44vh] w-full flex-shrink-0 flex flex-col border-b lg:h-auto lg:w-[360px] lg:border-r lg:border-b-0 ${
              darkMode ? 'bg-white border-gray-200' : 'bg-white border-gray-200'
            }`}>
              {/* Cart Header */}
              <div className="flex-shrink-0 px-4 py-3 flex items-center justify-between border-b border-gray-100 bg-gray-50/50">
                <div className="flex items-center gap-2">
                  <div className="h-7 w-7 rounded-md bg-[#714B67] flex items-center justify-center">
                    <Receipt className="h-3.5 w-3.5 text-white" />
                  </div>
                  <h3 className="text-sm font-extrabold tracking-tight">{t('cart', 'កន្ត្រក')}</h3>
                </div>
                <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-100 text-[#714B67]">
                  {cartItemCount} {t('items', 'items')}
                </span>
              </div>

              {/* Cart Items */}
              <div className="min-h-0 flex-1 overflow-y-auto px-3 py-3 space-y-2">
                {cart.length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center">
                    <ShoppingBag className="h-8 w-8 mb-2 text-gray-300" />
                    <p className="text-xs font-bold text-brand-muted">{t('cart_empty', 'កន្ត្រកទទេ')}</p>
                    <p className="text-[10px] text-brand-muted/60 mt-0.5">{t('select_products', 'ជ្រើសរើសទំនិញដើម្បីចាប់ផ្តើម')}</p>
                  </div>
                ) : (
                  cart.map(item => (
                    <div
                      key={item.product.id}
                      className="cart-item flex items-center gap-3 p-2 rounded border bg-gray-50/50 border-gray-100"
                    >
                      {/* Thumb */}
                      <div className="h-8 w-8 rounded overflow-hidden flex-shrink-0 flex items-center justify-center bg-white border border-gray-100">
                        {item.product.image ? (
                          <img src={item.product.image} className="h-full w-full object-cover" alt="" />
                        ) : (
                          <Package className="h-4 w-4 text-brand-muted/40" />
                        )}
                      </div>

                      {/* Info */}
                      <div className="flex-1 min-w-0">
                        <div className="text-[11px] font-bold truncate text-gray-800">{item.product.name}</div>
                        <div className="text-[11px] font-extrabold text-[#714B67] mt-0.5">${(item.product.price * item.quantity).toFixed(2)}</div>
                      </div>

                      {/* Qty controls */}
                      <div className="flex items-center gap-0.5 rounded p-0.5 border bg-white border-gray-200">
                        <button
                          onClick={(e) => { e.stopPropagation(); updateCartQty(item.product.id, -1); }}
                          className="h-5 w-5 rounded flex items-center justify-center hover:bg-red-50 text-gray-500 hover:text-[#e05038] transition text-xs"
                        >
                          <Minus className="h-3 w-3" />
                        </button>
                        <span className="w-5 text-center text-[11px] font-bold text-gray-850">{item.quantity}</span>
                        <button
                          onClick={(e) => { e.stopPropagation(); updateCartQty(item.product.id, 1); }}
                          className="h-5 w-5 rounded flex items-center justify-center hover:bg-green-50 text-gray-500 hover:text-[#2c8a3c] transition text-xs"
                        >
                          <Plus className="h-3 w-3" />
                        </button>
                      </div>
                    </div>
                  ))
                )}
              </div>

              {/* Cart Footer */}
              <div className="flex-shrink-0 px-3 pb-3 pt-2 space-y-3 border-t border-gray-200 bg-gray-50/30">
                {/* Customer + Mode selectors */}
                <div className="space-y-2">
                  <div className="relative">
                    <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">{t('customer', 'អតិថិជន')}</label>
                    
                    {selectedCustomerId ? (
                      // Beautiful active customer card
                      <div className="p-2.5 rounded border flex items-center justify-between gap-2.5 bg-gray-50 border-[#714B67]/20">
                        <div className="flex items-center gap-2 min-w-0">
                          <div className="h-7 w-7 rounded bg-[#714B67] text-white flex items-center justify-center font-bold text-xs">
                            {(() => {
                              const match = customers.find(c => String(c.id) === String(selectedCustomerId));
                              if (!match) return 'CU';
                              return match.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                            })()}
                          </div>
                          <div className="min-w-0">
                            <p className="text-[11px] font-bold truncate text-gray-800">{customers.find(c => String(c.id) === String(selectedCustomerId))?.name}</p>
                            <p className="text-[9px] text-brand-muted font-bold truncate">{customers.find(c => String(c.id) === String(selectedCustomerId))?.phone || 'No phone'}</p>
                          </div>
                        </div>
                        <button
                          onClick={() => setSelectedCustomerId('')}
                          className="h-5 w-5 rounded bg-red-50 hover:bg-red-100 text-[#e05038] flex items-center justify-center transition-all"
                        >
                          <X className="h-3 w-3" />
                        </button>
                      </div>
                    ) : (
                      // Select input
                      <div className="relative">
                        <UserCircle className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-brand-muted" />
                        <select
                          value={selectedCustomerId}
                          onChange={(e) => setSelectedCustomerId(e.target.value)}
                          className="w-full appearance-none py-1.5 pl-8 pr-8 text-[11px] font-semibold rounded border bg-gray-50 border-gray-200 text-gray-700"
                        >
                          <option value="">{t('walk_in', 'Walk-in (អតិថិជនទូទៅ)')}</option>
                          {customers.map(c => (
                            <option key={c.id} value={c.id}>{c.name} {c.phone && `(${c.phone})`}</option>
                          ))}
                        </select>
                        <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 h-3 w-3 text-brand-muted pointer-events-none" />
                      </div>
                    )}
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">{t('mode', 'Mode')}</label>
                      <select
                        value={orderStatus}
                        onChange={(e) => setOrderStatus(e.target.value)}
                        className="w-full py-1.5 px-2 text-[11px] font-semibold rounded border bg-gray-50 border-gray-200 text-gray-700"
                      >
                        <option value="completed">{t('mode_sell', 'លក់ Sell')}</option>
                        <option value="pending">{t('mode_hold', 'រង់ចាំ Hold')}</option>
                      </select>
                    </div>
                    <div>
                      <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">{t('receipt', 'Receipt')}</label>
                      <div className="py-1.5 px-2 text-[11px] font-bold rounded text-center bg-green-50 text-[#2c8a3c] border border-green-200">
                        {t('auto_print', 'Auto-Print')}
                      </div>
                    </div>
                  </div>
                </div>

                {/* Receipt Totals */}
                <div className="p-2.5 rounded border border-gray-200 bg-gray-50/50 space-y-1.5">
                  <div className="flex justify-between gap-3 text-[11px] font-medium text-brand-muted">
                    <span>{t('customer', 'Customer')}</span>
                    <span className="truncate text-right">{selectedCustomer?.name || t('walk_in', 'Walk-in')}</span>
                  </div>
                  <div className="flex justify-between items-center text-[11px] font-medium text-brand-muted">
                    <span>{t('subtotal', 'Subtotal')}</span>
                    <span>${getSubtotal().toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between items-center text-[11px] font-medium text-brand-muted">
                    <span>{t('tax', 'Tax (0%)')}</span>
                    <span>$0.00</span>
                  </div>
                  <div className="receipt-divider my-1 border-t border-dashed border-gray-300" />
                  <div className="flex justify-between items-center">
                    <span className="text-xs font-extrabold text-gray-800">{t('total', 'សរុប Total')}</span>
                    <span className="text-base font-black text-[#714B67]">${getGrandTotal().toFixed(2)}</span>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-2">
                  <button
                    onClick={clearCart}
                    title={t('clear_cart', 'Clear Cart')}
                    className="h-10 w-10 rounded border border-red-200 bg-red-50 text-[#e05038] hover:bg-red-100 flex items-center justify-center transition-all flex-shrink-0"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                  <button
                    disabled={cart.length === 0}
                    onClick={() => {
                      if (orderStatus === 'pending') {
                        handleCheckoutSubmit();
                      } else {
                        setPaymentModalOpen(true);
                      }
                    }}
                    className="bg-[#2c8a3c] hover:bg-[#257633] text-white flex-1 h-10 rounded text-[11px] font-extrabold flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                  >
                    <Zap className="h-3.5 w-3.5" />
                    <span>{orderStatus === 'pending' ? t('hold_order', 'ដាក់រង់ចាំ Hold') : t('checkout', 'ទូទាត់ Checkout')}</span>
                    <ArrowRight className="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            </aside>

            {/* ─── Right: Products ─── */}
            <main className="flex-1 min-h-0 flex flex-col overflow-hidden">
              {/* Search + Categories bar */}
              <div className="flex-shrink-0 px-4 py-3 space-y-3 border-b border-gray-200 bg-white">
                {/* Search input */}
                <div className="relative">
                  <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#714B67]" />
                  <input
                    type="text"
                    placeholder={t('search_placeholder', 'ស្វែងរកទំនិញ — barcode, SKU, ឈ្មោះ...')}
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={handleQuickAdd}
                    className="w-full py-2 pl-10 pr-4 text-sm font-medium rounded border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none"
                  />
                </div>

                {/* Category pills */}
                <div className="flex items-center gap-2 overflow-x-auto no-scrollbar pb-0.5">
                  {getCategories().map(cat => {
                    const isActive = selectedCategory === cat || (cat === 'All' && selectedCategory === '');
                    return (
                      <button
                        key={cat}
                        onClick={() => setSelectedCategory(cat === 'All' ? '' : cat)}
                        className={`px-3 py-1 rounded text-[11px] font-bold whitespace-nowrap transition-all ${
                          isActive
                            ? 'bg-[#714B67] text-white border border-[#714B67]'
                            : 'bg-gray-100 hover:bg-gray-200 border border-transparent text-gray-600'
                        }`}
                      >
                        {cat === 'All' ? t('all', 'ទាំងអស់') : cat}
                      </button>
                    );
                  })}
                </div>

                <div className="hidden grid-cols-3 gap-2 sm:grid">
                  <div className="rounded border border-gray-200 bg-white px-3 py-1.5">
                    <div className="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-brand-muted">
                      <Package className="h-3.5 w-3.5 text-[#714B67]" />
                      <span>{t('products_label', 'Products')}</span>
                    </div>
                    <div className="mt-0.5 text-xs font-black text-gray-800">{visibleProductCount}</div>
                  </div>
                  <div className="rounded border border-gray-200 bg-white px-3 py-1.5">
                    <div className="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-brand-muted">
                      <AlertTriangle className="h-3.5 w-3.5 text-[#ec9a29]" />
                      <span>{t('stock_alerts', 'Stock alerts')}</span>
                    </div>
                    <div className="mt-0.5 text-xs font-black text-gray-800">
                      {lowStockCount}
                      <span className="ml-1 text-[9px] font-bold text-brand-muted">/ {outOfStockCount} {t('out', 'out')}</span>
                    </div>
                  </div>
                  <div className="rounded border border-gray-200 bg-white px-3 py-1.5">
                    <div className="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-wider text-brand-muted">
                      <Clock className="h-3.5 w-3.5 text-[#00A09D]" />
                      <span>{t('pending', 'Pending')}</span>
                    </div>
                    <div className="mt-0.5 text-xs font-black text-gray-800">{pendingOrders.length}</div>
                  </div>
                </div>
              </div>

              {/* Product Grid */}
              <div className="flex-1 overflow-y-auto p-4 bg-gray-150">
                {getFilteredProducts().length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center animate-fade-in">
                    <Package className="h-12 w-12 text-gray-300 mb-2" />
                    <p className="text-xs font-bold text-brand-muted">{t('no_products', 'រកមិនឃើញទំនិញទេ')}</p>
                    <p className="text-[10px] text-brand-muted/60 mt-0.5">{t('no_products_subtitle', 'No products match your search')}</p>
                  </div>
                ) : (
                  <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    {getFilteredProducts().map((prod) => {
                      const isOutOfStock = prod.stock <= 0;
                      const isLowStock = prod.stock > 0 && prod.stock <= 5;
                      const inCartItem = cart.find(item => item.product.id === prod.id);

                      return (
                        <div
                          key={prod.id}
                          onClick={() => addToCart(prod)}
                          className={`bg-white border border-gray-200 rounded-md overflow-hidden cursor-pointer animate-slide-up group ${
                            isOutOfStock ? 'opacity-45 cursor-not-allowed' : ''
                          } ${inCartItem ? 'ring-2 ring-[#714B67]' : ''}`}
                        >
                          {/* Image area */}
                          <div className="aspect-[4/3] relative overflow-hidden flex items-center justify-center bg-gray-50 border-b border-gray-100">
                            {prod.image ? (
                              <img src={prod.image} alt={prod.name} className="h-full w-full object-cover" />
                            ) : (
                              <Package className="h-8 w-8 text-gray-300" />
                            )}

                            {/* Stock badge with dot */}
                            <span className={`absolute top-2 right-2 px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider flex items-center gap-1 ${
                              isOutOfStock ? 'bg-red-50 text-[#e05038] border border-red-100' : 
                              isLowStock ? 'bg-amber-50 text-[#d97706] border border-amber-100' : 
                              'bg-green-50 text-[#2c8a3c] border border-green-100'
                            }`}>
                              <span className={`h-1 w-1 rounded-full ${
                                isOutOfStock ? 'bg-[#e05038]' : 
                                isLowStock ? 'bg-[#d97706]' : 
                                'bg-[#2c8a3c]'
                              }`}></span>
                              {isOutOfStock ? t('out_of_stock', 'អស់') : `${prod.stock}`}
                            </span>

                            {/* In-cart quantity overlay */}
                            {inCartItem && (
                              <div className="absolute inset-0 bg-black/5 backdrop-blur-[0.5px] flex items-center justify-center">
                                <span className="bg-[#714B67] text-white rounded px-2.5 py-1 text-[11px] font-extrabold">
                                  ×{inCartItem.quantity}
                                </span>
                              </div>
                            )}

                            {/* Hover overlay */}
                            {!inCartItem && !isOutOfStock && (
                              <div className="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <div className="h-7 w-7 rounded bg-[#714B67] flex items-center justify-center shadow">
                                  <Plus className="h-4 w-4 text-white" />
                                </div>
                              </div>
                            )}
                          </div>

                          {/* Info */}
                          <div className="p-2.5 space-y-1">
                            <h4 className="text-[11px] font-bold truncate leading-tight text-gray-800 group-hover:text-[#714B67] transition-colors">{prod.name}</h4>
                            <div className="flex items-center justify-between">
                              <span className="text-xs font-extrabold text-[#714B67]">${prod.price.toFixed(2)}</span>
                              <span className="text-[8px] font-bold text-gray-400 truncate max-w-[60px] uppercase bg-gray-100 px-1 py-0.5 rounded">
                                {prod.sku || prod.category}
                              </span>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            </main>
          </>
        )}
      </div>

      {/* ═══ Pending Orders Drawer ═══ */}
      {pendingOrdersOpen && (
        <div className="fixed inset-0 z-50 modal-backdrop flex justify-end animate-fade-in" onClick={() => setPendingOrdersOpen(false)}>
          <div
            className={`w-full max-w-sm h-full flex flex-col shadow-glass-lg animate-slide-right ${
              darkMode ? 'bg-brand-surfDark' : 'bg-white'
            }`}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Drawer Header */}
            <div className="flex-shrink-0 px-5 py-4 flex items-center justify-between border-b border-gray-100">
              <div className="flex items-center gap-2">
                <Clock className="h-5 w-5 text-[#714B67]" />
                <h3 className="text-sm font-extrabold">{t('pending_orders_title', 'បញ្ជាទិញរង់ចាំ')}</h3>
              </div>
              <button
                onClick={() => setPendingOrdersOpen(false)}
                className="h-8 w-8 rounded-lg flex items-center justify-center transition hover:bg-gray-100 text-gray-400"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Drawer Body */}
            <div className="flex-1 overflow-y-auto p-4 space-y-3">
              {pendingOrders.length === 0 ? (
                <div className="h-full flex flex-col items-center justify-center text-center">
                  <Clock className="h-10 w-10 mb-3 text-gray-200" />
                  <p className="text-xs font-bold text-brand-muted">{t('no_pending_orders', 'មិនមាន order រង់ចាំទេ')}</p>
                </div>
              ) : (
                pendingOrders.map((order, idx) => (
                  <div
                    key={order.id}
                    onClick={() => { window.location.href = `?resume=${order.id}`; }}
                    className="p-4 rounded cursor-pointer transition-all animate-slide-up bg-gray-50 border border-gray-100 hover:border-[#714B67]/30"
                    style={{ animationDelay: `${idx * 50}ms`, animationFillMode: 'both' }}
                  >
                    <div className="flex justify-between items-start mb-2">
                      <div>
                        <span className="text-xs font-extrabold">{t('order', 'Order')} #{order.id}</span>
                        <div className="text-[9px] font-medium text-brand-muted mt-0.5 flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          <span>{new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                      </div>
                      <span className="text-sm font-black text-[#714B67]">${parseFloat(order.total).toFixed(2)}</span>
                    </div>

                    {order.notes && (
                      <div className="p-2 rounded text-[10px] mb-2 bg-amber-50 border border-amber-200">
                        <span className="font-bold text-amber-600 block">{t('note', 'Note')}:</span>
                        <span className="text-brand-muted">{order.notes}</span>
                      </div>
                    )}

                    <div className="flex justify-between items-center pt-2 border-t border-gray-100">
                      <span className="text-[10px] font-medium text-brand-muted">{order.item_lines} {t('items', 'items')}</span>
                      <span className="text-[10px] font-bold text-[#714B67] hover:text-[#00A09D] transition flex items-center gap-1">
                        {t('resume', 'បន្ត')} <ChevronRight className="h-3 w-3" />
                      </span>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}

      {/* ═══ Payment Modal ═══ */}
      {paymentModalOpen && (
        <div className="fixed inset-0 z-50 modal-backdrop flex items-center justify-center p-4 animate-fade-in" onClick={() => setPaymentModalOpen(false)}>
          <div
            className="w-full max-w-md rounded-lg p-6 border transition-all duration-300 bg-white border-gray-200 text-brand-textLight"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-4 border-b border-gray-100">
              <div className="flex items-center gap-3">
                <div className="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-[#714B67]">
                  <CreditCard className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="text-sm font-black tracking-tight text-gray-800">{t('payment_title', 'ទូទាត់ប្រាក់')}</h3>
                  <p className="text-[10px] text-brand-muted font-bold uppercase tracking-wider">{t('payment_subtitle', 'Checkout Processing')}</p>
                </div>
              </div>
              <button
                onClick={() => setPaymentModalOpen(false)}
                className="h-8 w-8 rounded-lg flex items-center justify-center transition-all hover:bg-gray-100 text-gray-400 hover:text-gray-700"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Total */}
            <div className="mt-4 p-4 rounded-lg flex items-center justify-between border bg-gray-50 border-gray-100">
              <div>
                <div className="text-[9px] font-bold uppercase tracking-widest text-brand-muted">{t('total_payable', 'ទឹកប្រាក់សរុប')}</div>
                <div className="text-xl font-black text-[#714B67] mt-0.5">${getGrandTotal().toFixed(2)}</div>
              </div>
              <span className="text-[10px] font-black uppercase bg-[#714B67]/10 text-[#714B67] px-3 py-1.5 rounded tracking-wider border border-[#714B67]/20">
                USD
              </span>
            </div>

            {/* Payment Method Tabs */}
            <div className="mt-5 space-y-2">
              <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block">{t('payment_method', 'វិធីបង់ប្រាក់')}</label>
              <div className="grid grid-cols-3 gap-2">
                {settings.pos_method_cash_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('cash')}
                    className={`p-3 rounded-lg border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-200 ${
                      paymentMethod === 'cash'
                        ? 'border-[#714B67] bg-[#714B67]/5 text-[#714B67] font-extrabold'
                        : 'border-gray-250 text-gray-500 hover:border-[#714B67]/30 hover:text-[#714B67]'
                    }`}
                  >
                    <Wallet className="h-4.5 w-4.5" />
                    <span className="text-[10px] font-bold">{t('cash', 'សាច់ប្រាក់')}</span>
                  </button>
                )}
                {settings.pos_method_khqr_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('khqr')}
                    className={`p-3 rounded-lg border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-200 ${
                      paymentMethod === 'khqr'
                        ? 'border-[#714B67] bg-[#714B67]/5 text-[#714B67] font-extrabold'
                        : 'border-gray-250 text-gray-500 hover:border-[#714B67]/30 hover:text-[#714B67]'
                    }`}
                  >
                    <QrCode className="h-4.5 w-4.5" />
                    <span className="text-[10px] font-bold">KHQR</span>
                  </button>
                )}
                {settings.pos_method_card_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('card')}
                    className={`p-3 rounded-lg border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-200 ${
                      paymentMethod === 'card'
                        ? 'border-[#714B67] bg-[#714B67]/5 text-[#714B67] font-extrabold'
                        : 'border-gray-250 text-gray-500 hover:border-[#714B67]/30 hover:text-[#714B67]'
                    }`}
                  >
                    <CreditCard className="h-4.5 w-4.5" />
                    <span className="text-[10px] font-bold">{t('card', 'Card')}</span>
                  </button>
                )}
              </div>
            </div>

            {/* Payment Details */}
            <div className="mt-4">
              {paymentMethod === 'cash' && (
                <div className="space-y-3 animate-fade-in">
                  <div>
                    <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">{t('cash_received', 'ប្រាក់ទទួលបាន')}</label>
                    <div className="relative">
                      <span className="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-black text-brand-muted">$</span>
                      <input
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        value={cashGiven}
                        onChange={(e) => setCashGiven(e.target.value)}
                        className="w-full py-2.5 pl-9 pr-4 text-lg font-black rounded border border-gray-200 text-gray-800 focus:outline-none focus:border-[#714B67]"
                      />
                    </div>
                    {/* Visual Bill Selector Pad */}
                    <div className="mt-3 space-y-1.5">
                      <div className="text-[8px] font-bold uppercase tracking-widest text-brand-muted">{t('quick_tender', 'Quick Tender Notes')}</div>
                      <div className="grid grid-cols-4 gap-1.5">
                        {[
                          { val: 1.00, label: '$1' },
                          { val: 5.00, label: '$5' },
                          { val: 10.00, label: '$10' },
                          { val: 20.00, label: '$20' },
                          { val: 50.00, label: '$50' },
                          { val: 100.00, label: '$100' },
                          { val: 2.50, label: '10K ៛' },
                          { val: 5.00, label: '20K ៛' },
                          { val: 12.50, label: '50K ៛' }
                        ].map(bill => (
                          <button
                            key={bill.label}
                            type="button"
                            onClick={() => {
                              const current = parseFloat(cashGiven) || 0;
                              setCashGiven((current + bill.val).toFixed(2));
                            }}
                            className="rounded border border-gray-200 bg-white py-1.5 text-[10px] font-black text-gray-700 hover:border-[#714B67]/50 hover:bg-[#714B67]/5 hover:text-[#714B67]"
                          >
                            {bill.label}
                          </button>
                        ))}
                        <button
                          type="button"
                          onClick={() => setCashGiven('')}
                          className="rounded border py-1.5 text-[10px] font-black border-red-200 bg-red-50 text-[#e05038] hover:bg-red-100"
                        >
                          C
                        </button>
                      </div>
                    </div>
                  </div>

                  {parseFloat(cashGiven) > 0 && (
                    <div className="p-3 rounded border border-green-200 bg-green-50/50 flex items-center justify-between animate-scale-in">
                      <span className="text-[11px] font-bold text-[#2c8a3c]">{t('change', 'ប្រាក់អាប់ Change')}</span>
                      <span className="text-lg font-black text-[#2c8a3c]">
                        ${Math.max(0, parseFloat(cashGiven) - getGrandTotal()).toFixed(2)}
                      </span>
                    </div>
                  )}
                </div>
              )}

              {paymentMethod === 'khqr' && (
                <div className="text-center space-y-3 animate-fade-in">
                  <div className="relative inline-block p-1 rounded-lg bg-gray-50 border border-gray-200 overflow-hidden">
                    <div className="relative qr-container inline-block border border-gray-100 rounded bg-white overflow-hidden p-2">
                      <img
                        src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(getKHQRString())}`}
                        alt="Bakong KHQR Code"
                        className="h-40 w-40 mx-auto rounded"
                      />
                    </div>
                  </div>
                  <div className="flex flex-col items-center justify-center gap-1">
                    <div className="text-[10px] font-black uppercase tracking-[0.2em] text-[#714B67] animate-pulse flex items-center gap-2 justify-center">
                      <span className="h-2 w-2 rounded-full bg-[#714B67] animate-ping"></span>
                      {t('waiting_khqr', 'កំពុងរង់ចាំការផ្ទេរប្រាក់ Bakong...')}
                    </div>
                    <span className="text-[9px] text-brand-muted font-bold uppercase tracking-wider">Syncing with Bakong Network</span>
                  </div>
                </div>
              )}

              {paymentMethod === 'card' && (
                <div className="p-4 rounded border border-gray-100 bg-gray-50/50 text-center animate-fade-in">
                  {cardSimulating ? (
                    <div className="space-y-3">
                      <div className="relative w-40 h-22 mx-auto bg-slate-800 rounded p-3 text-left text-white shadow-md border border-white/10">
                        <div className="h-5 w-7 bg-amber-400/80 rounded-sm relative shadow-inner"></div>
                        <div className="mt-3 text-[9px] font-mono tracking-widest opacity-80">•••• •••• •••• 8842</div>
                        <div className="mt-1 text-[7px] font-bold tracking-widest uppercase opacity-50">ASSOCIATE CARD</div>
                      </div>
                      <div className="space-y-2">
                        <div className="text-[11px] font-black text-[#714B67] flex items-center justify-center gap-1">
                          <span className="h-1.5 w-1.5 rounded-full bg-[#714B67] animate-ping"></span>
                          {cardProgress < 40 ? t('connecting_card', 'Connecting to card reader...') :
                           cardProgress < 85 ? 'Reading card chip & authenticating...' :
                           'Authorizing payment transaction...'}
                        </div>
                        <div className="w-full h-1.5 rounded bg-gray-200 overflow-hidden">
                          <div
                            className="bg-[#714B67] h-full transition-all duration-300 rounded"
                            style={{ width: `${cardProgress}%` }}
                          />
                        </div>
                      </div>
                    </div>
                  ) : (
                    <div className="space-y-3 py-2">
                      <div className="relative h-10 w-10 mx-auto rounded bg-[#714B67]/10 flex items-center justify-center border border-[#714B67]/20">
                        <CreditCard className="h-5 w-5 text-[#714B67]" />
                      </div>
                      <div>
                        <p className="text-xs font-black text-[#714B67] uppercase tracking-wider">{t('insert_card', 'បញ្ចូលកាត POS reader device')}</p>
                        <p className="text-[10px] text-brand-muted font-bold mt-0.5 max-w-[240px] mx-auto">{t('submit_handshake', 'Submit payment below to trigger card hardware handshake')}</p>
                      </div>
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* Modal Actions */}
            <div className="mt-5 flex flex-col gap-2">
              <button
                onClick={handleCheckoutSubmit}
                disabled={cardSimulating}
                className="bg-[#2c8a3c] hover:bg-[#257633] text-white w-full h-10 rounded text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
              >
                <Check className="h-4 w-4" />
                <span>{t('confirm_finish', 'បញ្ជាក់ និង បញ្ចប់ Confirm')}</span>
              </button>
              <button
                onClick={() => setPaymentModalOpen(false)}
                disabled={cardSimulating}
                className="w-full py-2 text-[10px] font-black text-brand-muted hover:text-[#714B67] transition-all duration-200 uppercase tracking-wider"
              >
                {t('cancel', 'បោះបង់ Cancel')}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
