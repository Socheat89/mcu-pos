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

  // Multi-Order states mimicking Odoo POS tabs
  const [orders, setOrders] = useState([
    { id: Date.now(), name: 'Order 1', cart: [], selectedCustomerId: '', orderStatus: 'completed' }
  ]);
  const [activeOrderId, setActiveOrderId] = useState(orders[0].id);

  // Keypad / Numpad state for Odoo POS controls
  const [keypadMode, setKeypadMode] = useState('quantity'); // 'quantity' | 'discount' | 'price'
  const [activeProductId, setActiveProductId] = useState(null);
  const [keypadValue, setKeypadValue] = useState('');

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

  // Dynamic references to active order details
  const activeOrder = orders.find(o => o.id === activeOrderId) || orders[0];
  const cart = activeOrder.cart;
  const selectedCustomerId = activeOrder.selectedCustomerId;
  const orderStatus = activeOrder.orderStatus;

  const updateActiveOrder = (updater) => {
    setOrders(prev => prev.map(o => o.id === activeOrderId ? { ...o, ...updater(o) } : o));
  };

  const setSelectedCustomerId = (val) => updateActiveOrder(() => ({ selectedCustomerId: val }));
  const setOrderStatus = (val) => updateActiveOrder(() => ({ orderStatus: val }));

  const addNewOrder = () => {
    const nextNum = orders.length + 1;
    const newOrd = {
      id: Date.now(),
      name: `Order ${nextNum}`,
      cart: [],
      selectedCustomerId: '',
      orderStatus: 'completed'
    };
    setOrders([...orders, newOrd]);
    setActiveOrderId(newOrd.id);
  };

  const removeOrder = (id, e) => {
    e.stopPropagation();
    if (orders.length === 1) {
      updateActiveOrder(() => ({ cart: [], selectedCustomerId: '', orderStatus: 'completed' }));
      setActiveProductId(null);
      return;
    }
    const index = orders.findIndex(o => o.id === id);
    const nextOrders = orders.filter(o => o.id !== id);
    setOrders(nextOrders);
    if (activeOrderId === id) {
      const nextActive = nextOrders[Math.max(0, index - 1)];
      setActiveOrderId(nextActive.id);
    }
  };

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
          restoredCart.push({ product: prod, quantity: item.quantity, discount: 0, customPrice: undefined });
        }
      });
      setOrders([
        {
          id: resumeOrder.id,
          name: `Resumed #${resumeOrder.id}`,
          cart: restoredCart,
          selectedCustomerId: resumeOrder.customer_id ? resumeOrder.customer_id.toString() : '',
          orderStatus: 'completed'
        }
      ]);
      setActiveOrderId(resumeOrder.id);
      if (restoredCart.length > 0) {
        setActiveProductId(restoredCart[0].product.id);
        setKeypadValue(String(restoredCart[0].quantity));
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
    let newCart;
    if (existing) {
      if (existing.quantity >= product.stock) {
        showToast('warning', t('toast_limit_stock', 'ដល់កម្រិតស្តុក'), t('toast_limit_stock_msg', 'មានតែ :qty ​គ្រាប់នៅសល់។').replace(':qty', product.stock));
        return;
      }
      newCart = cart.map(item =>
        item.product.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
      );
    } else {
      newCart = [...cart, { product, quantity: 1, discount: 0, customPrice: undefined }];
    }
    
    updateActiveOrder(() => ({ cart: newCart }));
    
    // Auto-select/focus added product
    setActiveProductId(product.id);
    const addedItem = newCart.find(item => item.product.id === product.id);
    if (keypadMode === 'quantity') {
      setKeypadValue(String(addedItem.quantity));
    } else if (keypadMode === 'discount') {
      setKeypadValue(String(addedItem.discount || 0));
    } else if (keypadMode === 'price') {
      setKeypadValue(String(addedItem.customPrice !== undefined ? addedItem.customPrice : addedItem.product.price));
    }
  };

  const updateCartQty = (productId, delta) => {
    const existing = cart.find(item => item.product.id === productId);
    if (!existing) return;
    const nextQty = existing.quantity + delta;
    let newCart;
    if (nextQty <= 0) {
      newCart = cart.filter(item => item.product.id !== productId);
      if (activeProductId === productId) {
        setActiveProductId(null);
      }
    } else {
      if (nextQty > existing.product.stock) {
        showToast('warning', t('toast_limit_stock', 'ដល់កម្រិតស្តុក'), t('toast_limit_stock_msg', 'មានតែ :qty ​គ្រាប់នៅសល់។').replace(':qty', existing.product.stock));
        return;
      }
      newCart = cart.map(item =>
        item.product.id === productId ? { ...item, quantity: nextQty } : item
      );
      if (activeProductId === productId && keypadMode === 'quantity') {
        setKeypadValue(String(nextQty));
      }
    }
    updateActiveOrder(() => ({ cart: newCart }));
  };

  const clearCart = () => {
    if (cart.length === 0) return;
    if (window.confirm(t('clear_cart_confirm', 'លុបទំនិញទាំងអស់ក្នុងកន្ត្រក?'))) {
      updateActiveOrder(() => ({ cart: [] }));
      setActiveProductId(null);
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

  // ─── Keypad Actions ───────────────────────────────────────
  const changeKeypadMode = (mode) => {
    setKeypadMode(mode);
    if (activeProductId) {
      const activeItem = cart.find(item => item.product.id === activeProductId);
      if (activeItem) {
        if (mode === 'quantity') {
          setKeypadValue(String(activeItem.quantity));
        } else if (mode === 'discount') {
          setKeypadValue(String(activeItem.discount || 0));
        } else if (mode === 'price') {
          setKeypadValue(String(activeItem.customPrice !== undefined ? activeItem.customPrice : activeItem.product.price));
        }
      }
    }
  };

  const handleSelectCartItem = (productId) => {
    setActiveProductId(productId);
    const activeItem = cart.find(item => item.product.id === productId);
    if (activeItem) {
      if (keypadMode === 'quantity') {
        setKeypadValue(String(activeItem.quantity));
      } else if (keypadMode === 'discount') {
        setKeypadValue(String(activeItem.discount || 0));
      } else if (keypadMode === 'price') {
        setKeypadValue(String(activeItem.customPrice !== undefined ? activeItem.customPrice : activeItem.product.price));
      }
    }
  };

  const handleKeypadPress = (val) => {
    if (!activeProductId) return;
    
    let currentValStr = keypadValue;
    if (val === 'backspace') {
      currentValStr = currentValStr.slice(0, -1);
      if (currentValStr === '') currentValStr = '0';
    } else if (val === '+/-') {
      if (currentValStr.startsWith('-')) {
        currentValStr = currentValStr.substring(1);
      } else if (currentValStr !== '0' && currentValStr !== '') {
        currentValStr = '-' + currentValStr;
      }
    } else if (val === '.') {
      if (!currentValStr.includes('.')) {
        currentValStr = (currentValStr || '0') + '.';
      }
    } else {
      if (currentValStr === '0' || currentValStr === '') {
        currentValStr = val;
      } else {
        currentValStr += val;
      }
    }
    
    setKeypadValue(currentValStr);
    const numVal = parseFloat(currentValStr) || 0;
    
    updateActiveOrder(order => {
      const updatedCart = order.cart.map(item => {
        if (item.product.id === activeProductId) {
          if (keypadMode === 'quantity') {
            if (numVal > item.product.stock) {
              showToast('warning', t('toast_limit_stock'), t('toast_limit_stock_msg').replace(':qty', item.product.stock));
              return item;
            }
            return { ...item, quantity: numVal };
          } else if (keypadMode === 'discount') {
            return { ...item, discount: Math.min(100, Math.max(0, numVal)) };
          } else if (keypadMode === 'price') {
            return { ...item, customPrice: Math.max(0, numVal) };
          }
        }
        return item;
      }).filter(item => item.quantity > 0 || (item.product.id === activeProductId && keypadMode !== 'quantity'));
      
      const hasActiveItem = updatedCart.some(item => item.product.id === activeProductId);
      if (!hasActiveItem && keypadMode === 'quantity') {
        setActiveProductId(null);
      }
      
      return { cart: updatedCart };
    });
  };

  // ─── Helpers ──────────────────────────────────────────────
  const getItemUnitPrice = (item) => {
    const base = item.customPrice !== undefined ? item.customPrice : item.product.price;
    const disc = item.discount || 0;
    return base * (1 - disc / 100);
  };

  const getItemTotal = (item) => {
    return getItemUnitPrice(item) * item.quantity;
  };

  const getSubtotal = () => cart.reduce((sum, item) => sum + getItemTotal(item), 0);
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
            <input type="hidden" name={`items[${index}][unit_price]`} value={getItemUnitPrice(item)} />
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
        <div className="glass px-4 py-2.5 sm:px-6 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200">
          {/* Left: Branding */}
          <div className="flex min-w-0 items-center gap-3">
            <div className="h-9 w-9 flex-shrink-0 rounded-md bg-brand-cyan flex items-center justify-center">
              <Layers className="h-4 w-4 text-white" />
            </div>
            <div className="min-w-0">
              <div className="max-w-[180px] truncate text-[8px] font-bold uppercase tracking-wider text-brand-muted sm:max-w-none">{settings.store_label}</div>
              <div className="flex items-center gap-2">
                <h1 className="text-xs font-black tracking-tight text-brand-cyan">Mekong POS</h1>
                <span className="hidden xs:inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[8px] font-bold bg-brand-success/15 text-brand-success border border-brand-success/20">
                  <span className="h-1 w-1 rounded-full bg-brand-success animate-ping"></span>
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
              className={`flex items-center gap-2 rounded-lg px-3 py-2 text-[11px] font-extrabold transition-all border shadow-sm ${
                darkMode
                  ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt text-brand-cyan border-brand-cyan/20 hover:border-brand-cyan/40 hover:shadow-glow-cyan/5'
                  : 'bg-white hover:bg-gray-50 text-brand-cyan border-gray-200 hover:border-brand-cyan/30'
              }`}
            >
              <ArrowLeft className="h-3.5 w-3.5" />
              <span>{t('exit', 'Dashboard')}</span>
            </a>

            {/* Analytics toggle */}
            <button
              onClick={() => setAnalyticsViewOpen(!analyticsViewOpen)}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-2 text-[11px] font-bold transition-all duration-300 ${
                analyticsViewOpen
                  ? 'bg-brand-cyan text-white shadow-sm'
                  : `${darkMode ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt text-brand-textDark border border-white/5' : 'bg-white hover:bg-gray-50 text-brand-textLight border border-gray-200'}`
              }`}
            >
              <BarChart2 className="h-3.5 w-3.5" />
              <span className="hidden sm:inline">{analyticsViewOpen ? t('sell', 'លក់ទំនិញ') : t('reports', 'របាយការណ៍')}</span>
            </button>

            {/* Pending orders */}
            <button
              onClick={() => setPendingOrdersOpen(true)}
              className={`relative flex items-center gap-1.5 rounded-lg px-3 py-2 text-[11px] font-bold transition-all ${
                darkMode ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt text-brand-textDark border border-white/5' : 'bg-white hover:bg-gray-50 text-brand-textLight border border-gray-200'
              }`}
            >
              <Clock className="h-3.5 w-3.5 text-brand-violet" />
              <span className="hidden sm:inline">{t('pending', 'រង់ចាំ')}</span>
              {pendingOrders.length > 0 && (
                <span className="absolute -top-1.5 -right-1.5 bg-brand-danger text-white rounded-full text-[9px] font-bold px-1.5 py-0.5 badge-pulse">
                  {pendingOrders.length}
                </span>
              )}
            </button>

            {/* Language Switcher Dropdown */}
            <div className="relative" ref={langMenuRef}>
              <button
                onClick={() => setLangMenuOpen(!langMenuOpen)}
                className={`h-8 px-2.5 rounded-lg flex items-center gap-1.5 transition-all text-[11px] font-bold ${
                  darkMode ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt border border-white/5' : 'bg-white hover:bg-gray-50 border border-gray-200'
                }`}
              >
                <Languages className="h-3.5 w-3.5 text-brand-cyan" />
                <span className="uppercase">{currentLang === 'en' ? 'EN' : currentLang === 'km' ? 'KH' : 'ZH'}</span>
                <ChevronDown className="h-3 w-3 text-brand-muted" />
              </button>

              {langMenuOpen && (
                <div className={`absolute right-0 mt-1.5 w-36 rounded-xl shadow-glass-lg border backdrop-blur-xl p-1 z-50 animate-scale-in ${
                  darkMode ? 'bg-brand-surfDark/95 border-white/5 text-brand-textDark' : 'bg-white/95 border-gray-100 text-brand-textLight'
                }`}>
                  {languages.map(lang => (
                    <button
                      key={lang.code}
                      onClick={() => changeLang(lang.code)}
                      className={`w-full flex items-center justify-between rounded-lg px-2.5 py-1.5 text-[11px] font-bold transition-all ${
                        currentLang === lang.code
                          ? 'bg-gradient-to-r from-brand-cyan/20 to-brand-violet/20 text-brand-cyan border border-brand-cyan/10'
                          : `hover:bg-brand-cyan/10 text-brand-muted hover:text-brand-cyan border border-transparent`
                      }`}
                    >
                      <span className="flex items-center gap-1.5">
                        <span>{lang.flag}</span>
                        <span>{lang.label}</span>
                      </span>
                      {currentLang === lang.code && <Check className="h-3 w-3 text-brand-cyan" />}
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Dark mode */}
            <button
              onClick={() => setDarkMode(!darkMode)}
              className={`h-8 w-8 rounded-lg flex items-center justify-center transition-all ${
                darkMode ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt border border-white/5' : 'bg-white hover:bg-gray-50 border border-gray-200'
              }`}
            >
              {darkMode ? <Sun className="h-3.5 w-3.5 text-amber-400" /> : <Moon className="h-3.5 w-3.5 text-slate-500" />}
            </button>

            {/* Clock */}
            <div className={`hidden md:flex items-center gap-1.5 rounded-lg px-3 py-2 text-[11px] font-mono font-bold tracking-wider ${
              darkMode ? 'bg-brand-surfDark border border-white/5 text-brand-cyan' : 'bg-white border border-gray-200 text-brand-cyan'
            }`}>
              <Activity className="h-3 w-3" />
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
            {/* ─── Left: Products ─── */}
            <main className="flex-1 min-h-0 flex flex-col overflow-hidden">
              {/* Search + Categories bar */}
              <div className={`flex-shrink-0 px-3 py-3 sm:px-5 space-y-3 border-b ${darkMode ? 'border-white/5' : 'border-gray-200'}`}>
                {/* Search input */}
                <div className="relative">
                  <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-brand-cyan" />
                  <input
                    type="text"
                    placeholder={t('search_placeholder', 'ស្វែងរកទំនិញ — barcode, SKU, ឈ្មោះ...')}
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={handleQuickAdd}
                    className={`w-full py-2.5 pl-10 pr-4 text-sm font-medium rounded-lg border transition-all duration-300 ${
                      darkMode
                        ? 'bg-brand-surfDark border-white/5 text-brand-textDark placeholder-slate-500'
                        : 'bg-white border-gray-200 text-brand-textLight placeholder-gray-400'
                    }`}
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
                        className={`px-3.5 py-1.5 rounded-lg text-[11px] font-bold whitespace-nowrap transition-all duration-300 ${
                          isActive
                            ? 'pill-active'
                            : `${darkMode ? 'bg-brand-surfDark border border-white/5 text-slate-400 hover:text-brand-cyan hover:border-brand-cyan/20' : 'bg-white border border-gray-200 text-gray-500 hover:text-brand-cyan hover:border-brand-cyan/30'}`
                        }`}
                      >
                        {cat === 'All' ? t('all', 'ទាំងអស់') : cat}
                      </button>
                    );
                  })}
                </div>

                <div className="hidden grid-cols-3 gap-2 sm:grid">
                  <div className={`rounded-lg border px-3 py-2 ${darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'}`}>
                    <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-brand-muted">
                      <Package className="h-3.5 w-3.5 text-brand-cyan" />
                      <span>{t('products_label', 'Products')}</span>
                    </div>
                    <div className="mt-1 text-sm font-black">{visibleProductCount}</div>
                  </div>
                  <div className={`rounded-lg border px-3 py-2 ${darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'}`}>
                    <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-brand-muted">
                      <AlertTriangle className="h-3.5 w-3.5 text-brand-warning" />
                      <span>{t('stock_alerts', 'Stock alerts')}</span>
                    </div>
                    <div className="mt-1 text-sm font-black">
                      {lowStockCount}
                      <span className="ml-1 text-[10px] font-bold text-brand-muted">/ {outOfStockCount} {t('out', 'out')}</span>
                    </div>
                  </div>
                  <div className={`rounded-lg border px-3 py-2 ${darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'}`}>
                    <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-brand-muted">
                      <Clock className="h-3.5 w-3.5 text-brand-violet" />
                      <span>{t('pending', 'Pending')}</span>
                    </div>
                    <div className="mt-1 text-sm font-black">{pendingOrders.length}</div>
                  </div>
                </div>
              </div>

              {/* Product Grid */}
              <div className="flex-1 overflow-y-auto p-3 sm:p-5">
                {getFilteredProducts().length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center animate-fade-in">
                    <Package className="h-16 w-16 text-brand-muted/30 mb-4" />
                    <p className="text-sm font-bold text-brand-muted">{t('no_products', 'រកមិនឃើញទំនិញទេ')}</p>
                    <p className="text-xs text-brand-muted/60 mt-1">{t('no_products_subtitle', 'No products match your search')}</p>
                  </div>
                ) : (
                  <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    {getFilteredProducts().map((prod, idx) => {
                      const isOutOfStock = prod.stock <= 0;
                      const isLowStock = prod.stock > 0 && prod.stock <= 5;
                      const inCartItem = cart.find(item => item.product.id === prod.id);

                      return (
                        <div
                          key={prod.id}
                          onClick={() => addToCart(prod)}
                          className={`glass-card rounded-2xl overflow-hidden cursor-pointer animate-slide-up group transition-all duration-300 ${
                            isOutOfStock ? 'opacity-40 cursor-not-allowed' : ''
                          } ${inCartItem ? 'ring-2 ring-brand-cyan shadow-glow-cyan' : ''}`}
                          style={{ animationDelay: `${idx * 20}ms`, animationFillMode: 'both' }}
                        >
                          {/* Image area */}
                          <div className={`aspect-[4/3] relative overflow-hidden flex items-center justify-center ${
                            darkMode ? 'bg-brand-bgDark/40' : 'bg-slate-50'
                          }`}>
                            {prod.image ? (
                              <img src={prod.image} alt={prod.name} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            ) : (
                              <Package className={`h-9 w-9 transition-transform duration-500 group-hover:scale-110 ${darkMode ? 'text-slate-700' : 'text-slate-300'}`} />
                            )}

                            {/* Stock badge with pulsing dot */}
                            <span className={`absolute top-2 right-2 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider flex items-center gap-1.5 ${
                              isOutOfStock ? 'stock-out bg-brand-danger/10 text-brand-danger' : 
                              isLowStock ? 'stock-low bg-brand-warning/10 text-brand-warning' : 
                              'stock-ok bg-brand-success/10 text-brand-success'
                            }`}>
                              <span className={`h-1.5 w-1.5 rounded-full ${
                                isOutOfStock ? 'bg-brand-danger' : 
                                isLowStock ? 'bg-brand-warning animate-pulse' : 
                                'bg-brand-success'
                              }`}></span>
                              {isOutOfStock ? t('out_of_stock', 'អស់') : isLowStock ? `${prod.stock}` : `${prod.stock}`}
                            </span>

                            {/* In-cart quantity overlay */}
                            {inCartItem && (
                              <div className="product-overlay" style={{ opacity: 1 }}>
                                <span className="bg-brand-cyan text-white rounded-full px-3 py-1 text-xs font-black shadow-sm">
                                  ×{inCartItem.quantity}
                                </span>
                              </div>
                            )}

                            {/* Hover overlay */}
                            {!inCartItem && !isOutOfStock && (
                              <div className="product-overlay bg-black/5 dark:bg-black/20">
                                <div className="h-8 w-8 rounded-full bg-brand-cyan flex items-center justify-center shadow-sm transform scale-90 group-hover:scale-100 transition-transform duration-200">
                                  <Plus className="h-4.5 w-4.5 text-white" />
                                </div>
                              </div>
                            )}
                          </div>

                          {/* Info */}
                          <div className="p-3.5 space-y-1.5">
                            <h4 className="text-xs font-bold truncate leading-tight text-slate-800 dark:text-slate-200 group-hover:text-brand-cyan transition-colors">{prod.name}</h4>
                            <div className="flex items-center justify-between">
                              <span className="text-sm font-black text-brand-cyan">${prod.price.toFixed(2)}</span>
                              <span className="text-[9px] font-bold text-brand-muted truncate max-w-[70px] uppercase bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">
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

            {/* ─── Right: Cart Sidebar ─── */}
            <aside className={`h-[44vh] w-full flex-shrink-0 flex flex-col border-t lg:h-auto lg:w-[360px] lg:border-l lg:border-t-0 ${
              darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'
            }`}>
              {/* Odoo POS Order Tabs */}
              <div className="flex-shrink-0 px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                {orders.map(order => {
                  const isActive = order.id === activeOrderId;
                  return (
                    <div
                      key={order.id}
                      onClick={() => {
                        setActiveOrderId(order.id);
                        if (order.cart.length > 0) {
                          setActiveProductId(order.cart[0].product.id);
                          setKeypadValue(String(order.cart[0].quantity));
                        } else {
                          setActiveProductId(null);
                          setKeypadValue('');
                        }
                      }}
                      className={`flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-bold cursor-pointer transition-all border ${
                        isActive
                          ? 'bg-brand-cyan border-brand-cyan text-white shadow-sm'
                          : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-100'
                      }`}
                    >
                      <span>{order.name}</span>
                      <button
                        onClick={(e) => removeOrder(order.id, e)}
                        className={`h-3.5 w-3.5 rounded flex items-center justify-center text-[9px] hover:bg-black/10 hover:text-black font-black ${
                          isActive ? 'text-white/80 hover:text-white' : 'text-gray-400 hover:text-gray-600'
                        }`}
                      >
                        ✕
                      </button>
                    </div>
                  );
                })}
                <button
                  onClick={addNewOrder}
                  className="px-2.5 py-1.5 rounded bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 flex items-center justify-center transition-all cursor-pointer shadow-sm text-[11px] font-bold"
                >
                  <Plus className="h-3.5 w-3.5" />
                </button>
              </div>

              {/* Cart Header */}
              <div className={`flex-shrink-0 px-5 py-3 flex items-center justify-between border-b ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
                <div className="flex items-center gap-2">
                  <div className="h-6 w-6 rounded bg-brand-cyan flex items-center justify-center">
                    <Receipt className="h-3.5 w-3.5 text-white" />
                  </div>
                  <h3 className="text-xs font-bold tracking-tight text-slate-800">{t('cart', 'កន្ត្រក')}</h3>
                </div>
                <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-100 text-brand-cyan">
                  {cartItemCount} {t('items', 'items')}
                </span>
              </div>

              {/* Cart Items */}
              <div className="min-h-0 flex-1 overflow-y-auto px-3 py-3 sm:px-4 space-y-1 bg-white">
                {cart.length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center py-10">
                    <ShoppingBag className="h-8 w-8 mb-2 text-gray-300" />
                    <p className="text-xs font-bold text-brand-muted">{t('cart_empty', 'កន្ត្រកទទេ')}</p>
                    <p className="text-[10px] text-brand-muted/60 mt-0.5">{t('select_products', 'ជ្រើសរើសទំនិញដើម្បីចាប់ផ្តើម')}</p>
                  </div>
                ) : (
                  cart.map(item => (
                    <div
                      key={item.product.id}
                      onClick={() => handleSelectCartItem(item.product.id)}
                      className={`cart-item flex flex-col gap-1 p-2 rounded cursor-pointer transition-all border ${
                        item.product.id === activeProductId
                          ? 'bg-brand-cyan/5 border-brand-cyan ring-1 ring-brand-cyan shadow-sm'
                          : 'border-transparent hover:bg-gray-50/50'
                      }`}
                    >
                      <div className="flex items-center justify-between gap-2">
                        <div className="font-bold text-[11px] truncate text-slate-800">{item.product.name}</div>
                        <div className="font-black text-[11px] text-brand-cyan">${getItemTotal(item).toFixed(2)}</div>
                      </div>
                      
                      <div className="flex flex-col gap-0.5 text-[9px] text-brand-muted font-semibold pl-2 border-l border-brand-cyan/30">
                        <div>
                          {item.quantity} {item.quantity === 1 ? 'Unit' : 'Units'} at{' '}
                          {item.customPrice !== undefined ? (
                            <span>
                              <span className="line-through text-gray-400 mr-1">${item.product.price.toFixed(2)}</span>
                              <span className="text-brand-cyan font-bold">${item.customPrice.toFixed(2)}</span>
                            </span>
                          ) : (
                            `$${item.product.price.toFixed(2)}`
                          )}{' '}
                          / Unit
                        </div>
                        {item.discount > 0 && (
                          <div className="text-brand-danger font-bold">
                            With a {item.discount}% discount
                          </div>
                        )}
                      </div>
                    </div>
                  ))
                )}
              </div>

              {/* Cart Footer */}
              <div className="flex-shrink-0 px-3 pb-3 pt-2 bg-gray-50 border-t border-gray-200 flex flex-col gap-2">
                {/* Odoo style Customer Select Button */}
                <div className="relative">
                  {selectedCustomerId ? (
                    <button
                      onClick={() => setSelectedCustomerId('')}
                      className="w-full flex items-center justify-between gap-3 px-3 py-2 bg-white hover:bg-gray-100 border border-brand-cyan rounded text-[11px] font-bold text-brand-cyan transition-all shadow-sm"
                    >
                      <span className="flex items-center gap-2 truncate">
                        <UserCircle className="h-4 w-4 text-brand-cyan flex-shrink-0" />
                        <span className="truncate">{selectedCustomer?.name || 'Walk-in'}</span>
                      </span>
                      <X className="h-3.5 w-3.5 hover:text-brand-danger" />
                    </button>
                  ) : (
                    <div className="relative">
                      <UserCircle className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400" />
                      <select
                        value={selectedCustomerId}
                        onChange={(e) => setSelectedCustomerId(e.target.value)}
                        className="w-full appearance-none py-1.5 pl-8 pr-8 text-[11px] font-bold rounded border border-gray-200 bg-white text-gray-600 focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan"
                      >
                        <option value="">{t('customer', 'អតិថិជន')} : {t('walk_in', 'Walk-in')}</option>
                        {customers.map(c => (
                          <option key={c.id} value={c.id}>{c.name} {c.phone && `(${c.phone})`}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 h-3 w-3 text-gray-400 pointer-events-none" />
                    </div>
                  )}
                </div>

                {/* Keypad Grid & Checkout Action */}
                <div className="flex gap-2 items-stretch">
                  {/* Numpad Keypad (left/center) */}
                  <div className="flex-1 grid grid-cols-4 gap-1 select-none">
                    {[
                      { val: '1', label: '1' },
                      { val: '2', label: '2' },
                      { val: '3', label: '3' },
                      { val: 'qty', label: 'Qty', isMode: true, modeName: 'quantity' },
                      { val: '4', label: '4' },
                      { val: '5', label: '5' },
                      { val: '6', label: '6' },
                      { val: 'disc', label: 'Disc', isMode: true, modeName: 'discount' },
                      { val: '7', label: '7' },
                      { val: '8', label: '8' },
                      { val: '9', label: '9' },
                      { val: 'price', label: 'Price', isMode: true, modeName: 'price' },
                      { val: '+/-', label: '+/-' },
                      { val: '0', label: '0' },
                      { val: '.', label: '.' },
                      { val: 'backspace', label: '⌫' }
                    ].map(btn => {
                      const isSelectedMode = btn.isMode && keypadMode === btn.modeName;
                      return (
                        <button
                          key={btn.val}
                          type="button"
                          onClick={() => {
                            if (btn.isMode) {
                              changeKeypadMode(btn.modeName);
                            } else {
                              handleKeypadPress(btn.val);
                            }
                          }}
                          className={`h-9 rounded text-center text-xs font-black transition-all border ${
                            isSelectedMode
                              ? 'bg-brand-cyan border-brand-cyan text-white shadow-sm'
                              : btn.isMode
                              ? 'bg-gray-100 border-gray-200 text-gray-700 hover:bg-gray-200'
                              : btn.val === 'backspace'
                              ? 'bg-gray-100 border-gray-200 text-brand-danger hover:bg-red-50'
                              : 'bg-white border-gray-200 text-gray-800 hover:bg-gray-100'
                          }`}
                        >
                          {btn.label}
                        </button>
                      );
                    })}
                  </div>

                  {/* Payment checkout Action Button (right) */}
                  <div className="w-[96px] flex flex-col gap-1">
                    <button
                      onClick={clearCart}
                      title={t('clear_cart', 'Clear Cart')}
                      className="h-8 border border-brand-danger/20 bg-brand-danger/5 text-brand-danger hover:bg-brand-danger/10 rounded flex items-center justify-center transition-all cursor-pointer"
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
                      className="flex-1 bg-brand-success text-white hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed rounded flex flex-col items-center justify-center gap-1 transition-all cursor-pointer shadow-sm"
                    >
                      <Zap className="h-4 w-4" />
                      <span className="text-[10px] font-black tracking-wider uppercase">
                        {orderStatus === 'pending' ? 'Hold' : 'Payment'}
                      </span>
                      <ArrowRight className="h-3 w-3" />
                    </button>
                  </div>
                </div>

                {/* Receipt Totals summary */}
                <div className="p-2.5 rounded bg-white border border-gray-200 space-y-1 text-[11px] font-medium text-brand-muted">
                  <div className="flex justify-between items-center">
                    <span>{t('subtotal', 'Subtotal')}</span>
                    <span>${getSubtotal().toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between items-center text-xs font-black text-slate-800 border-t border-gray-100 pt-1.5 mt-1">
                    <span>{t('total', 'Total')}</span>
                    <span className="text-sm font-black text-brand-cyan">${getGrandTotal().toFixed(2)}</span>
                  </div>
                </div>
              </div>
            </aside>
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
            <div className={`flex-shrink-0 px-5 py-4 flex items-center justify-between border-b ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
              <div className="flex items-center gap-2">
                <Clock className="h-5 w-5 text-brand-violet" />
                <h3 className="text-sm font-extrabold">{t('pending_orders_title', 'បញ្ជាទិញរង់ចាំ')}</h3>
              </div>
              <button
                onClick={() => setPendingOrdersOpen(false)}
                className={`h-8 w-8 rounded-lg flex items-center justify-center transition ${
                  darkMode ? 'hover:bg-brand-bgDark text-brand-muted' : 'hover:bg-gray-100 text-gray-400'
                }`}
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Drawer Body */}
            <div className="flex-1 overflow-y-auto p-4 space-y-3">
              {pendingOrders.length === 0 ? (
                <div className="h-full flex flex-col items-center justify-center text-center">
                  <Clock className={`h-10 w-10 mb-3 ${darkMode ? 'text-slate-700' : 'text-gray-200'}`} />
                  <p className="text-xs font-bold text-brand-muted">{t('no_pending_orders', 'មិនមាន order រង់ចាំទេ')}</p>
                </div>
              ) : (
                pendingOrders.map((order, idx) => (
                  <div
                    key={order.id}
                    onClick={() => { window.location.href = `?resume=${order.id}`; }}
                    className={`p-4 rounded-xl cursor-pointer transition-all animate-slide-up group ${
                      darkMode
                        ? 'bg-brand-bgDark/50 border border-white/5 hover:border-brand-cyan/30'
                        : 'bg-gray-50 border border-gray-100 hover:border-brand-cyan/30'
                    }`}
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
                      <span className="text-sm font-black text-brand-cyan">${parseFloat(order.total).toFixed(2)}</span>
                    </div>

                    {order.notes && (
                      <div className={`p-2 rounded-lg text-[10px] mb-2 ${
                        darkMode ? 'bg-brand-warning/10 border border-brand-warning/20' : 'bg-amber-50 border border-amber-200'
                      }`}>
                        <span className="font-bold text-brand-warning block">{t('note', 'Note')}:</span>
                        <span className="text-brand-muted">{order.notes}</span>
                      </div>
                    )}

                    <div className={`flex justify-between items-center pt-2 border-t ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
                      <span className="text-[10px] font-medium text-brand-muted">{order.item_lines} {t('items', 'items')}</span>
                      <span className="text-[10px] font-bold text-brand-cyan group-hover:text-brand-violet transition flex items-center gap-1">
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
            className={`w-full max-w-md rounded-3xl shadow-glass-lg p-6 border transition-all duration-300 ${
              darkMode ? 'bg-brand-surfDark/95 border-white/10 text-brand-textDark shadow-glow-violet' : 'bg-white/95 border-slate-200 text-brand-textLight'
            }`}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Modal Header */}
            <div className={`flex items-center justify-between pb-4 border-b ${darkMode ? 'border-white/5' : 'border-slate-100'}`}>
              <div className="flex items-center gap-3">
                <div className="h-10 w-10 rounded-2xl bg-gradient-to-br from-brand-cyan to-brand-violet text-white flex items-center justify-center shadow-glow-cyan">
                  <CreditCard className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="text-sm font-black tracking-tight">{t('payment_title', 'ទូទាត់ប្រាក់')}</h3>
                  <p className="text-[10px] text-brand-muted font-bold uppercase tracking-wider">{t('payment_subtitle', 'Checkout Processing')}</p>
                </div>
              </div>
              <button
                onClick={() => setPaymentModalOpen(false)}
                className={`h-8 w-8 rounded-xl flex items-center justify-center transition-all ${
                  darkMode ? 'hover:bg-brand-bgDark text-brand-muted hover:text-white' : 'hover:bg-slate-100 text-slate-400 hover:text-slate-700'
                }`}
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Total */}
            <div className={`mt-4 p-4 rounded-2xl flex items-center justify-between border ${
              darkMode ? 'bg-brand-bgDark/60 border-white/5' : 'bg-slate-50 border-slate-100'
            }`}>
              <div>
                <div className="text-[9px] font-bold uppercase tracking-widest text-brand-muted">{t('total_payable', 'ទឹកប្រាក់សរុប')}</div>
                <div className="text-2xl font-black text-gradient mt-0.5">ef{getGrandTotal().toFixed(2)}</div>
              </div>
              <span className="text-[9px] font-black uppercase bg-brand-cyan/15 text-brand-cyan px-3 py-1.5 rounded-full tracking-wider border border-brand-cyan/25 shadow-glow-cyan">
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
                        ? 'border-brand-cyan bg-brand-cyan text-white font-extrabold shadow-sm'
                        : 'border-gray-200 text-gray-600 hover:bg-gray-50 bg-white'
                    }`}
                  >
                    <Wallet className="h-4 w-4" />
                    <span className="text-[10px] font-bold">{t('cash', 'សាច់ប្រាក់')}</span>
                  </button>
                )}
                {settings.pos_method_khqr_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('khqr')}
                    className={`p-3 rounded-lg border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-200 ${
                      paymentMethod === 'khqr'
                        ? 'border-brand-cyan bg-brand-cyan text-white font-extrabold shadow-sm'
                        : 'border-gray-200 text-gray-600 hover:bg-gray-50 bg-white'
                    }`}
                  >
                    <QrCode className="h-4 w-4" />
                    <span className="text-[10px] font-bold">KHQR</span>
                  </button>
                )}
                {settings.pos_method_card_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('card')}
                    className={`p-3 rounded-lg border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-200 ${
                      paymentMethod === 'card'
                        ? 'border-brand-cyan bg-brand-cyan text-white font-extrabold shadow-sm'
                        : 'border-gray-200 text-gray-600 hover:bg-gray-50 bg-white'
                    }`}
                  >
                    <CreditCard className="h-4 w-4" />
                    <span className="text-[10px] font-bold">{t('card', 'Card')}</span>
                  </button>
                )}
              </div>
            </div>

            {/* Payment Details */}
            <div className="mt-4">
              {paymentMethod === 'cash' && (
                <div className="space-y-3.5 animate-fade-in">
                  <div>
                    <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1.5">{t('cash_received', 'ប្រាក់ទទួលបាន')}</label>
                    <div className="relative">
                      <span className="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-black text-brand-muted">$</span>
                      <input
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        value={cashGiven}
                        onChange={(e) => setCashGiven(e.target.value)}
                        className={`w-full py-2.5 pl-9 pr-4 text-lg font-black rounded-lg border transition-all duration-200 ${
                          darkMode
                            ? 'bg-brand-bgDark border-white/5 text-brand-textDark focus:border-brand-cyan/50'
                            : 'bg-white border-slate-200 text-brand-textLight focus:border-brand-cyan/50 focus:ring-1 focus:ring-brand-cyan'
                        }`}
                      />
                    </div>
                    {/* Visual Bill Selector Pad */}
                    <div className="mt-3 space-y-2">
                      <div className="text-[8px] font-extrabold uppercase tracking-widest text-brand-muted">{t('quick_tender', 'Quick Tender Notes')}</div>
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
                            className={`rounded-xl border py-2 text-[10px] font-black transition-all ${
                              darkMode
                                ? 'border-white/5 bg-brand-bgDark text-brand-textDark hover:border-brand-cyan/50 hover:bg-brand-cyan/10 hover:text-brand-cyan'
                                : 'border-slate-200 bg-white text-brand-textLight hover:border-brand-cyan/50 hover:bg-brand-cyan/5 hover:text-brand-cyan'
                            }`}
                          >
                            {bill.label}
                          </button>
                        ))}
                        <button
                          type="button"
                          onClick={() => setCashGiven('')}
                          className="rounded-xl border py-2 text-[10px] font-black border-brand-danger/20 bg-brand-danger/5 text-brand-danger hover:bg-brand-danger/10"
                        >
                          C
                        </button>
                      </div>
                    </div>
                  </div>

                  {parseFloat(cashGiven) > 0 && (
                    <div className="p-3.5 rounded-2xl border border-brand-success/20 bg-brand-success/10 flex items-center justify-between animate-scale-in">
                      <span className="text-[11px] font-extrabold text-brand-success">{t('change', 'ប្រាក់អាប់ Change')}</span>
                      <span className="text-xl font-black text-brand-success">
                        ${Math.max(0, parseFloat(cashGiven) - getGrandTotal()).toFixed(2)}
                      </span>
                    </div>
                  )}
                </div>
              )}

              {paymentMethod === 'khqr' && (
                <div className="text-center space-y-4 animate-fade-in">
                  <div className="relative inline-block p-1 rounded-3xl bg-slate-100 border border-slate-200 overflow-hidden shadow-inner">
                    <div className="relative qr-container inline-block border border-slate-300/40 rounded-2xl bg-white overflow-hidden p-3.5 z-10">
                      <img
                        src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(getKHQRString())}`}
                        alt="Bakong KHQR Code"
                        className="h-44 w-44 mx-auto rounded-xl relative z-10"
                      />
                      <div className="absolute inset-x-0 h-0.5 bg-red-500 shadow-[0_0_10px_#f43f5e] animate-scanner-laser top-3.5 z-20"></div>
                    </div>
                  </div>
                  <div className="flex flex-col items-center justify-center gap-1.5">
                    <div className="text-[10px] font-black uppercase tracking-[0.25em] text-brand-cyan animate-pulse flex items-center gap-2 justify-center">
                      <span className="h-2 w-2 rounded-full bg-brand-cyan animate-ping"></span>
                      {t('waiting_khqr', 'កំពុងរង់ចាំការផ្ទេរប្រាក់ Bakong...')}
                    </div>
                    <span className="text-[9px] text-brand-muted font-bold uppercase tracking-wider">Syncing with Bakong Network</span>
                  </div>
                </div>
              )}

              {paymentMethod === 'card' && (
                <div className={`p-6 rounded-2xl text-center border animate-fade-in ${
                  darkMode ? 'bg-brand-bgDark/60 border-white/5' : 'bg-slate-50 border-slate-100'
                }`}>
                  {cardSimulating ? (
                    <div className="space-y-4">
                      <div className="relative w-44 h-24 mx-auto bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-3 text-left text-white shadow-xl overflow-hidden border border-white/10">
                        <div className="h-6 w-8 bg-amber-400/80 rounded-md relative shadow-inner flex items-center justify-center overflow-hidden">
                          <div className="absolute inset-0 bg-[linear-gradient(90deg,transparent_50%,rgba(0,0,0,0.1)_50%)] bg-[length:4px_100%]"></div>
                        </div>
                        <div className="mt-4 text-[9px] font-mono tracking-widest opacity-80">•••• •••• •••• 8842</div>
                        <div className="mt-2 text-[7px] font-bold tracking-widest uppercase opacity-50">ASSOCIATE CARD</div>
                        <div className="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-brand-cyan to-brand-violet animate-pulse"></div>
                      </div>
                      <div className="space-y-2">
                        <div className="text-xs font-black text-brand-cyan flex items-center justify-center gap-1">
                          <span className="h-1.5 w-1.5 rounded-full bg-brand-cyan animate-ping"></span>
                          {cardProgress < 40 ? t('connecting_card', 'Connecting to card reader...') :
                           cardProgress < 85 ? 'Reading card chip & authenticating...' :
                           'Authorizing payment transaction...'}
                        </div>
                        <div className={`w-full h-2 rounded-full overflow-hidden ${darkMode ? 'bg-brand-bgDark' : 'bg-slate-200'}`}>
                          <div
                            className="bg-gradient-to-r from-brand-cyan to-brand-violet h-full transition-all duration-300 rounded-full"
                            style={{ width: `${cardProgress}%` }}
                          />
                        </div>
                        <div className="text-[10px] text-brand-muted font-black tracking-wider uppercase">${cardProgress}% Completed</div>
                      </div>
                    </div>
                  ) : (
                    <div className="space-y-3.5 py-4">
                      <div className="relative h-12 w-12 mx-auto rounded-full bg-brand-cyan/15 flex items-center justify-center border border-brand-cyan/25 animate-float">
                        <CreditCard className="h-6 w-6 text-brand-cyan" />
                      </div>
                      <div>
                        <p className="text-xs font-black text-brand-cyan uppercase tracking-wider">{t('insert_card', 'បញ្ចូលកាត POS reader device')}</p>
                        <p className="text-[10px] text-brand-muted font-bold mt-1 max-w-[240px] mx-auto">{t('submit_handshake', 'Submit payment below to trigger card hardware handshake')}</p>
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
                className="btn-primary w-full h-11 text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <Check className="h-4 w-4" />
                <span>{t('confirm_finish', 'បញ្ជាក់ និង បញ្ចប់ Confirm')}</span>
              </button>
              <button
                onClick={() => setPaymentModalOpen(false)}
                disabled={cardSimulating}
                className="w-full py-2.5 text-[10px] font-black text-brand-muted hover:text-brand-cyan transition-all duration-300 uppercase tracking-wider"
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
