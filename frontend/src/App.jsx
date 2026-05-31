import React, { useState, useEffect, useRef } from 'react';
import {
  Search,
  ShoppingBag,
  Trash2,
  ArrowRight,
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
  LayoutGrid,
  Activity,
  Layers,
  TrendingUp,
  BarChart2,
  AlertTriangle,
  Package,
  Hash,
  UserCircle,
  Receipt,
  Zap,
  ChevronRight,
  Store,
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
  const [darkMode, setDarkMode] = useState(true); // Dark-first

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
      showToast('info', 'បានស្ដារ Order', `កំពុងបន្ត order #${resumeOrder.id}`);
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
      showToast('warning', 'អស់ស្តុក', `${product.name} មិនមានក្នុងស្តុកទេ។`);
      return;
    }
    const existing = cart.find(item => item.product.id === product.id);
    if (existing) {
      if (existing.quantity >= product.stock) {
        showToast('warning', 'ដល់កម្រិតស្តុក', `មានតែ ${product.stock} ​គ្រាប់នៅសល់។`);
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
        showToast('warning', 'ដល់កម្រិតស្តុក', `មានតែ ${existing.product.stock} ​គ្រាប់នៅសល់។`);
        return;
      }
      setCart(cart.map(item =>
        item.product.id === productId ? { ...item, quantity: nextQty } : item
      ));
    }
  };

  const clearCart = () => {
    if (cart.length === 0) return;
    if (window.confirm('លុបទំនិញទាំងអស់ក្នុងកន្ត្រក?')) {
      setCart([]);
      showToast('info', 'បានលុប', 'កន្ត្រកទទេហើយ។');
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
          showToast('success', 'បានបន្ថែម', `${match.name} បានបញ្ចូលក្នុងកន្ត្រក។`);
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
        showToast('error', 'ប្រាក់មិនគ្រប់', 'ចំនួនទឹកប្រាក់តិចជាងសរុប។');
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
    showToast('success', 'ជោគជ័យ!', 'កំពុងដំណើរការ...');
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
        <div className="accent-line" />
        <div className={`glass px-5 py-3 flex items-center justify-between ${darkMode ? '' : ''}`}>
          {/* Left: Branding */}
          <div className="flex items-center gap-3">
            <div className="h-9 w-9 rounded-xl bg-gradient-to-br from-brand-cyan to-brand-violet flex items-center justify-center shadow-glow-cyan">
              <Layers className="h-4.5 w-4.5 text-white" />
            </div>
            <div>
              <div className="text-[9px] font-bold uppercase tracking-[0.3em] text-brand-muted">{settings.store_label}</div>
              <h1 className="text-sm font-extrabold tracking-tight text-gradient leading-tight">Mekong POS</h1>
            </div>
          </div>

          {/* Right: Controls */}
          <div className="flex items-center gap-2">
            {/* Analytics toggle */}
            <button
              onClick={() => setAnalyticsViewOpen(!analyticsViewOpen)}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-2 text-[11px] font-bold transition-all duration-300 ${
                analyticsViewOpen
                  ? 'bg-gradient-to-r from-brand-cyan to-brand-violet text-white shadow-glow-cyan'
                  : `${darkMode ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt text-brand-textDark border border-white/5' : 'bg-white hover:bg-gray-50 text-brand-textLight border border-gray-200'}`
              }`}
            >
              <BarChart2 className="h-3.5 w-3.5" />
              <span className="hidden sm:inline">{analyticsViewOpen ? 'លក់ទំនិញ' : 'របាយការណ៍'}</span>
            </button>

            {/* Pending orders */}
            <button
              onClick={() => setPendingOrdersOpen(true)}
              className={`relative flex items-center gap-1.5 rounded-lg px-3 py-2 text-[11px] font-bold transition-all ${
                darkMode ? 'bg-brand-surfDark hover:bg-brand-surfDarkAlt text-brand-textDark border border-white/5' : 'bg-white hover:bg-gray-50 text-brand-textLight border border-gray-200'
              }`}
            >
              <Clock className="h-3.5 w-3.5 text-brand-violet" />
              <span className="hidden sm:inline">រង់ចាំ</span>
              {pendingOrders.length > 0 && (
                <span className="absolute -top-1.5 -right-1.5 bg-brand-danger text-white rounded-full text-[9px] font-bold px-1.5 py-0.5 badge-pulse">
                  {pendingOrders.length}
                </span>
              )}
            </button>

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
      <div className="flex-1 flex overflow-hidden">

        {analyticsViewOpen ? (
          /* ═══ Analytics View ═══ */
          <div className="flex-1 overflow-y-auto p-5 animate-fade-in">
            <div className={`rounded-2xl p-6 ${darkMode ? 'bg-brand-surfDark border border-white/5' : 'bg-white border border-gray-200'} shadow-glass`}>
              <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-2">
                  <TrendingUp className="h-5 w-5 text-brand-cyan" />
                  <h2 className="text-base font-extrabold tracking-tight">របាយការណ៍លក់ និងស្តុក</h2>
                </div>
                <button
                  onClick={() => setAnalyticsViewOpen(false)}
                  className="text-[10px] font-bold uppercase tracking-wider text-brand-muted hover:text-brand-cyan transition"
                >
                  បិទ ✕
                </button>
              </div>

              <div className="grid md:grid-cols-3 gap-5">
                {/* Category Sales */}
                <div className={`md:col-span-2 p-5 rounded-xl ${darkMode ? 'bg-brand-bgDark/50 border border-white/5' : 'bg-gray-50 border border-gray-100'}`}>
                  <h3 className="text-[10px] font-bold text-brand-muted mb-4 uppercase tracking-wider">ការលក់តាមប្រភេទ (USD)</h3>
                  <div className="h-64">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={getCategorySalesData()}>
                        <XAxis dataKey="name" stroke="#64748B" fontSize={11} tickLine={false} axisLine={false} />
                        <YAxis stroke="#64748B" fontSize={11} tickLine={false} axisLine={false} />
                        <Tooltip
                          contentStyle={{
                            background: darkMode ? 'rgba(19, 24, 37, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            border: darkMode ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(0,0,0,0.1)',
                            borderRadius: '12px',
                            color: darkMode ? '#E2E8F0' : '#1E293B',
                            backdropFilter: 'blur(8px)'
                          }}
                        />
                        <Bar dataKey="sales" fill="url(#cyanVioletGrad)" radius={[6, 6, 0, 0]}>
                          <defs>
                            <linearGradient id="cyanVioletGrad" x1="0" y1="0" x2="0" y2="1">
                              <stop offset="0%" stopColor="#06B6D4" />
                              <stop offset="100%" stopColor="#8B5CF6" />
                            </linearGradient>
                          </defs>
                        </Bar>
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </div>

                {/* Stock Levels */}
                <div className={`p-5 rounded-xl ${darkMode ? 'bg-brand-bgDark/50 border border-white/5' : 'bg-gray-50 border border-gray-100'}`}>
                  <h3 className="text-[10px] font-bold text-brand-muted mb-4 uppercase tracking-wider">ស្តុកបច្ចុប្បន្ន</h3>
                  <div className="h-64">
                    <ResponsiveContainer width="100%" height="100%">
                      <AreaChart data={getStockLevelsData()}>
                        <defs>
                          <linearGradient id="cyanAreaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stopColor="#06B6D4" stopOpacity={0.4} />
                            <stop offset="100%" stopColor="#06B6D4" stopOpacity={0.0} />
                          </linearGradient>
                        </defs>
                        <XAxis dataKey="name" stroke="#64748B" fontSize={9} tickLine={false} />
                        <Tooltip
                          contentStyle={{
                            background: darkMode ? 'rgba(19, 24, 37, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            border: darkMode ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(0,0,0,0.1)',
                            borderRadius: '12px',
                            color: darkMode ? '#E2E8F0' : '#1E293B'
                          }}
                        />
                        <Area type="monotone" dataKey="stock" stroke="#06B6D4" fill="url(#cyanAreaGrad)" strokeWidth={2} />
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
            <main className="flex-1 flex flex-col overflow-hidden">
              {/* Search + Categories bar */}
              <div className={`flex-shrink-0 px-5 py-3 space-y-3 border-b ${darkMode ? 'border-white/5' : 'border-gray-200'}`}>
                {/* Search input */}
                <div className="relative">
                  <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-brand-cyan" />
                  <input
                    type="text"
                    placeholder="ស្វែងរកទំនិញ — barcode, SKU, ឈ្មោះ..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={handleQuickAdd}
                    className={`w-full py-2.5 pl-10 pr-4 text-sm font-medium rounded-xl border transition-all duration-300 ${
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
                        {cat === 'All' ? '🏷️ ទាំងអស់' : cat}
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* Product Grid */}
              <div className="flex-1 overflow-y-auto p-5">
                {getFilteredProducts().length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center animate-fade-in">
                    <Package className="h-16 w-16 text-brand-muted/30 mb-4" />
                    <p className="text-sm font-bold text-brand-muted">រកមិនឃើញទំនិញទេ</p>
                    <p className="text-xs text-brand-muted/60 mt-1">No products match your search</p>
                  </div>
                ) : (
                  <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    {getFilteredProducts().map((prod, idx) => {
                      const isOutOfStock = prod.stock <= 0;
                      const isLowStock = prod.stock > 0 && prod.stock <= 5;
                      const inCartItem = cart.find(item => item.product.id === prod.id);

                      return (
                        <div
                          key={prod.id}
                          onClick={() => addToCart(prod)}
                          className={`glass-card rounded-xl overflow-hidden cursor-pointer animate-slide-up ${
                            isOutOfStock ? 'opacity-40 cursor-not-allowed' : ''
                          } ${inCartItem ? 'ring-2 ring-brand-cyan/40' : ''}`}
                          style={{ animationDelay: `${idx * 30}ms`, animationFillMode: 'both' }}
                        >
                          {/* Image area */}
                          <div className={`aspect-[4/3] relative overflow-hidden flex items-center justify-center ${
                            darkMode ? 'bg-brand-bgDark/50' : 'bg-gray-50'
                          }`}>
                            {prod.image ? (
                              <img src={prod.image} alt={prod.name} className="h-full w-full object-cover" />
                            ) : (
                              <Package className={`h-8 w-8 ${darkMode ? 'text-slate-700' : 'text-gray-200'}`} />
                            )}

                            {/* Stock badge */}
                            <span className={`absolute top-2 right-2 px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wide ${
                              isOutOfStock ? 'stock-out' : isLowStock ? 'stock-low' : 'stock-ok'
                            }`}>
                              {isOutOfStock ? 'អស់' : isLowStock ? `${prod.stock} left` : `${prod.stock}`}
                            </span>

                            {/* In-cart quantity overlay */}
                            {inCartItem && (
                              <div className="product-overlay" style={{ opacity: 1 }}>
                                <span className="bg-gradient-to-r from-brand-cyan to-brand-violet text-white rounded-full px-3 py-1 text-xs font-extrabold shadow-lg">
                                  ×{inCartItem.quantity}
                                </span>
                              </div>
                            )}

                            {/* Hover overlay */}
                            {!inCartItem && !isOutOfStock && (
                              <div className="product-overlay">
                                <div className="h-8 w-8 rounded-full bg-white/90 dark:bg-brand-surfDark/90 flex items-center justify-center shadow-lg">
                                  <Plus className="h-4 w-4 text-brand-cyan" />
                                </div>
                              </div>
                            )}
                          </div>

                          {/* Info */}
                          <div className="p-3 space-y-1">
                            <h4 className="text-xs font-bold truncate leading-tight">{prod.name}</h4>
                            <div className="flex items-center justify-between">
                              <span className="text-sm font-extrabold text-brand-cyan">${prod.price.toFixed(2)}</span>
                              <span className="text-[9px] font-medium text-brand-muted truncate max-w-[60px]">
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
            <aside className={`w-[340px] flex-shrink-0 flex flex-col border-l ${
              darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'
            }`}>
              {/* Cart Header */}
              <div className={`flex-shrink-0 px-5 py-3.5 flex items-center justify-between border-b ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
                <div className="flex items-center gap-2">
                  <div className="h-7 w-7 rounded-lg bg-gradient-to-br from-brand-cyan to-brand-violet flex items-center justify-center">
                    <Receipt className="h-3.5 w-3.5 text-white" />
                  </div>
                  <h3 className="text-sm font-extrabold tracking-tight">កន្ត្រក</h3>
                </div>
                <span className={`text-[10px] font-bold px-2.5 py-1 rounded-full ${
                  darkMode ? 'bg-brand-bgDark text-brand-cyan' : 'bg-gray-100 text-brand-cyan'
                }`}>
                  {cart.reduce((sum, item) => sum + item.quantity, 0)} items
                </span>
              </div>

              {/* Cart Items */}
              <div className="flex-1 overflow-y-auto px-4 py-3 space-y-2">
                {cart.length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-center">
                    <ShoppingBag className={`h-10 w-10 mb-3 ${darkMode ? 'text-slate-700' : 'text-gray-200'}`} />
                    <p className="text-xs font-bold text-brand-muted">កន្ត្រកទទេ</p>
                    <p className="text-[10px] text-brand-muted/60 mt-0.5">ជ្រើសរើសទំនិញដើម្បីចាប់ផ្តើម</p>
                  </div>
                ) : (
                  cart.map(item => (
                    <div
                      key={item.product.id}
                      className={`cart-item flex items-center gap-3 p-2.5 rounded-xl transition ${
                        darkMode ? 'bg-brand-bgDark/40 border border-white/5' : 'bg-gray-50 border border-gray-100'
                      }`}
                    >
                      {/* Thumb */}
                      <div className={`h-9 w-9 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center ${
                        darkMode ? 'bg-brand-bgDark' : 'bg-gray-100'
                      }`}>
                        {item.product.image ? (
                          <img src={item.product.image} className="h-full w-full object-cover" alt="" />
                        ) : (
                          <Package className="h-4 w-4 text-brand-muted/40" />
                        )}
                      </div>

                      {/* Info */}
                      <div className="flex-1 min-w-0">
                        <div className="text-[11px] font-bold truncate">{item.product.name}</div>
                        <div className="text-[11px] font-extrabold text-brand-cyan mt-0.5">${(item.product.price * item.quantity).toFixed(2)}</div>
                      </div>

                      {/* Qty controls */}
                      <div className={`flex items-center gap-0.5 rounded-full p-0.5 border ${
                        darkMode ? 'bg-brand-surfDark border-white/5' : 'bg-white border-gray-200'
                      }`}>
                        <button
                          onClick={(e) => { e.stopPropagation(); updateCartQty(item.product.id, -1); }}
                          className="h-6 w-6 rounded-full flex items-center justify-center hover:bg-brand-danger/20 hover:text-brand-danger transition text-brand-muted text-xs"
                        >
                          <Minus className="h-3 w-3" />
                        </button>
                        <span className="w-5 text-center text-[11px] font-bold">{item.quantity}</span>
                        <button
                          onClick={(e) => { e.stopPropagation(); updateCartQty(item.product.id, 1); }}
                          className="h-6 w-6 rounded-full flex items-center justify-center hover:bg-brand-success/20 hover:text-brand-success transition text-brand-muted text-xs"
                        >
                          <Plus className="h-3 w-3" />
                        </button>
                      </div>
                    </div>
                  ))
                )}
              </div>

              {/* Cart Footer */}
              <div className={`flex-shrink-0 px-4 pb-4 pt-2 space-y-3 border-t ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
                {/* Customer + Mode selectors */}
                <div className="space-y-2">
                  <div className="relative">
                    <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">អតិថិជន</label>
                    <div className="relative">
                      <UserCircle className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-brand-muted" />
                      <select
                        value={selectedCustomerId}
                        onChange={(e) => setSelectedCustomerId(e.target.value)}
                        className={`w-full appearance-none py-2 pl-8 pr-8 text-[11px] font-medium rounded-lg border transition ${
                          darkMode ? 'bg-brand-bgDark border-white/5 text-brand-textDark' : 'bg-gray-50 border-gray-200 text-brand-textLight'
                        }`}
                      >
                        <option value="">Walk-in (អតិថិជនទូទៅ)</option>
                        {customers.map(c => (
                          <option key={c.id} value={c.id}>{c.name} {c.phone && `(${c.phone})`}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 h-3 w-3 text-brand-muted pointer-events-none" />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">Mode</label>
                      <select
                        value={orderStatus}
                        onChange={(e) => setOrderStatus(e.target.value)}
                        className={`w-full py-2 px-2.5 text-[11px] font-medium rounded-lg border transition ${
                          darkMode ? 'bg-brand-bgDark border-white/5 text-brand-textDark' : 'bg-gray-50 border-gray-200'
                        }`}
                      >
                        <option value="completed">លក់ Sell</option>
                        <option value="pending">រង់ចាំ Hold</option>
                      </select>
                    </div>
                    <div>
                      <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1">Receipt</label>
                      <div className={`py-2 px-2.5 text-[11px] font-bold rounded-lg text-center ${
                        darkMode ? 'bg-brand-success/10 text-brand-success border border-brand-success/20' : 'bg-green-50 text-brand-success border border-green-200'
                      }`}>
                        Auto-Print
                      </div>
                    </div>
                  </div>
                </div>

                {/* Receipt Totals */}
                <div className={`p-3 rounded-xl space-y-1.5 ${darkMode ? 'bg-brand-bgDark/50 border border-white/5' : 'bg-gray-50 border border-gray-100'}`}>
                  <div className="flex justify-between items-center text-[11px] font-medium text-brand-muted">
                    <span>Subtotal</span>
                    <span>${getSubtotal().toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between items-center text-[11px] font-medium text-brand-muted">
                    <span>Tax (0%)</span>
                    <span>$0.00</span>
                  </div>
                  <div className="receipt-divider my-1.5" />
                  <div className="flex justify-between items-center">
                    <span className="text-xs font-extrabold">សរុប Total</span>
                    <span className="text-lg font-black text-gradient">${getGrandTotal().toFixed(2)}</span>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-2">
                  <button
                    onClick={clearCart}
                    title="Clear Cart"
                    className="h-10 w-10 rounded-xl flex items-center justify-center border border-brand-danger/20 bg-brand-danger/10 text-brand-danger hover:bg-brand-danger/20 transition-all flex-shrink-0"
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
                    className="btn-primary flex-1 h-10 text-[11px] font-extrabold flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
                  >
                    <Zap className="h-3.5 w-3.5" />
                    <span>{orderStatus === 'pending' ? 'ដាក់រង់ចាំ Hold' : 'ទូទាត់ Checkout'}</span>
                    <ArrowRight className="h-3.5 w-3.5" />
                  </button>
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
                <h3 className="text-sm font-extrabold">បញ្ជាទិញរង់ចាំ</h3>
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
                  <p className="text-xs font-bold text-brand-muted">មិនមាន order រង់ចាំទេ</p>
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
                        <span className="text-xs font-extrabold">Order #{order.id}</span>
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
                        <span className="font-bold text-brand-warning block">Note:</span>
                        <span className="text-brand-muted">{order.notes}</span>
                      </div>
                    )}

                    <div className={`flex justify-between items-center pt-2 border-t ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
                      <span className="text-[10px] font-medium text-brand-muted">{order.item_lines} items</span>
                      <span className="text-[10px] font-bold text-brand-cyan group-hover:text-brand-violet transition flex items-center gap-1">
                        បន្ត <ChevronRight className="h-3 w-3" />
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
            className={`w-full max-w-md rounded-2xl shadow-glass-lg p-6 animate-scale-in ${
              darkMode ? 'bg-brand-surfDark border border-white/5 text-brand-textDark' : 'bg-white border border-gray-200 text-brand-textLight'
            }`}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Modal Header */}
            <div className={`flex items-center justify-between pb-4 border-b ${darkMode ? 'border-white/5' : 'border-gray-100'}`}>
              <div className="flex items-center gap-3">
                <div className="h-9 w-9 rounded-xl bg-gradient-to-br from-brand-cyan to-brand-violet text-white flex items-center justify-center shadow-glow-cyan">
                  <CreditCard className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="text-sm font-extrabold">ទូទាត់ប្រាក់</h3>
                  <p className="text-[10px] text-brand-muted">Checkout Processing</p>
                </div>
              </div>
              <button
                onClick={() => setPaymentModalOpen(false)}
                className={`h-8 w-8 rounded-lg flex items-center justify-center transition ${
                  darkMode ? 'hover:bg-brand-bgDark text-brand-muted' : 'hover:bg-gray-100 text-gray-400'
                }`}
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Total */}
            <div className={`mt-4 p-4 rounded-xl flex items-center justify-between ${
              darkMode ? 'bg-brand-bgDark/50 border border-white/5' : 'bg-gray-50 border border-gray-100'
            }`}>
              <div>
                <div className="text-[9px] font-bold uppercase tracking-widest text-brand-muted">ទឹកប្រាក់សរុប</div>
                <div className="text-xl font-black text-gradient mt-0.5">${getGrandTotal().toFixed(2)}</div>
              </div>
              <span className="text-[9px] font-bold uppercase bg-brand-cyan/15 text-brand-cyan px-2.5 py-1 rounded-full tracking-wider border border-brand-cyan/20">
                USD
              </span>
            </div>

            {/* Payment Method Tabs */}
            <div className="mt-5 space-y-2">
              <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block">វិធីបង់ប្រាក់</label>
              <div className="grid grid-cols-3 gap-2">
                {settings.pos_method_cash_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('cash')}
                    className={`p-3 rounded-xl border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-300 ${
                      paymentMethod === 'cash'
                        ? 'border-brand-cyan bg-brand-cyan/10 text-brand-cyan font-bold shadow-glow-cyan/20'
                        : `${darkMode ? 'border-white/5 text-brand-muted hover:border-brand-cyan/20' : 'border-gray-200 text-gray-500 hover:border-brand-cyan/30'}`
                    }`}
                  >
                    <Wallet className="h-5 w-5" />
                    <span className="text-[10px]">សាច់ប្រាក់</span>
                  </button>
                )}
                {settings.pos_method_khqr_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('khqr')}
                    className={`p-3 rounded-xl border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-300 ${
                      paymentMethod === 'khqr'
                        ? 'border-brand-cyan bg-brand-cyan/10 text-brand-cyan font-bold shadow-glow-cyan/20'
                        : `${darkMode ? 'border-white/5 text-brand-muted hover:border-brand-cyan/20' : 'border-gray-200 text-gray-500 hover:border-brand-cyan/30'}`
                    }`}
                  >
                    <QrCode className="h-5 w-5" />
                    <span className="text-[10px]">KHQR</span>
                  </button>
                )}
                {settings.pos_method_card_enabled === '1' && (
                  <button
                    onClick={() => setPaymentMethod('card')}
                    className={`p-3 rounded-xl border text-center flex flex-col items-center justify-center gap-1.5 transition-all duration-300 ${
                      paymentMethod === 'card'
                        ? 'border-brand-cyan bg-brand-cyan/10 text-brand-cyan font-bold shadow-glow-cyan/20'
                        : `${darkMode ? 'border-white/5 text-brand-muted hover:border-brand-cyan/20' : 'border-gray-200 text-gray-500 hover:border-brand-cyan/30'}`
                    }`}
                  >
                    <CreditCard className="h-5 w-5" />
                    <span className="text-[10px]">Card</span>
                  </button>
                )}
              </div>
            </div>

            {/* Payment Details */}
            <div className="mt-4">
              {paymentMethod === 'cash' && (
                <div className="space-y-3 animate-fade-in">
                  <div>
                    <label className="text-[9px] font-bold uppercase tracking-wider text-brand-muted block mb-1.5">ប្រាក់ទទួលបាន</label>
                    <div className="relative">
                      <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-black text-brand-muted">$</span>
                      <input
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        value={cashGiven}
                        onChange={(e) => setCashGiven(e.target.value)}
                        className={`w-full py-3 pl-8 pr-4 text-base font-extrabold rounded-xl border transition ${
                          darkMode
                            ? 'bg-brand-bgDark border-white/5 text-brand-textDark'
                            : 'bg-gray-50 border-gray-200 text-brand-textLight'
                        }`}
                      />
                    </div>
                  </div>

                  {parseFloat(cashGiven) > 0 && (
                    <div className="p-3 rounded-xl border border-brand-success/20 bg-brand-success/10 flex items-center justify-between animate-scale-in">
                      <span className="text-[11px] font-bold text-brand-success">ប្រាក់អាប់ Change</span>
                      <span className="text-lg font-black text-brand-success">
                        ${Math.max(0, parseFloat(cashGiven) - getGrandTotal()).toFixed(2)}
                      </span>
                    </div>
                  )}
                </div>
              )}

              {paymentMethod === 'khqr' && (
                <div className="text-center space-y-3 animate-fade-in">
                  <div className="qr-container inline-block">
                    <img
                      src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(getKHQRString())}`}
                      alt="Bakong KHQR Code"
                      className="h-40 w-40 mx-auto rounded-lg"
                    />
                  </div>
                  <div className="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-danger animate-pulse">
                    កំពុងរង់ចាំការផ្ទេរប្រាក់ Bakong...
                  </div>
                </div>
              )}

              {paymentMethod === 'card' && (
                <div className={`p-5 rounded-xl text-center animate-fade-in ${
                  darkMode ? 'bg-brand-bgDark/50 border border-white/5' : 'bg-gray-50 border border-gray-100'
                }`}>
                  {cardSimulating ? (
                    <div className="space-y-3">
                      <div className="text-xs font-bold text-brand-cyan">Connecting to card reader...</div>
                      <div className={`w-full h-1.5 rounded-full overflow-hidden ${darkMode ? 'bg-brand-bgDark' : 'bg-gray-200'}`}>
                        <div
                          className="bg-gradient-to-r from-brand-cyan to-brand-violet h-full transition-all duration-300 rounded-full"
                          style={{ width: `${cardProgress}%` }}
                        />
                      </div>
                      <div className="text-[10px] text-brand-muted font-bold">{cardProgress}%</div>
                    </div>
                  ) : (
                    <div className="space-y-2">
                      <CreditCard className="mx-auto h-8 w-8 text-brand-cyan animate-float" />
                      <p className="text-xs font-bold text-brand-muted">បញ្ចូលកាត POS reader device</p>
                      <p className="text-[10px] text-brand-muted/60">Submit to initialize handshake</p>
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
                className="btn-primary w-full h-11 text-xs font-extrabold flex items-center justify-center gap-1.5"
              >
                <Check className="h-4 w-4" />
                <span>បញ្ជាក់ និង បញ្ចប់ Confirm</span>
              </button>
              <button
                onClick={() => setPaymentModalOpen(false)}
                disabled={cardSimulating}
                className="w-full py-2.5 text-[11px] font-bold text-brand-muted hover:text-brand-cyan transition"
              >
                បោះបង់ Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
