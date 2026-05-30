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
  Info,
  TrendingUp,
  BarChart2,
  Settings as SettingsIcon,
  User,
  Folder,
  Sparkles,
  AlertTriangle,
  Moon,
  Sun,
  LayoutGrid,
  CheckCircle2,
  Activity,
  Layers,
  ArrowUpRight
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

// --- Bakong KHQR Generator (NBC Standard) ---
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
  str += formatTag('00', '01'); // Payload Format Indicator
  str += formatTag('01', '11'); // Initiation Method (Static)

  // Merchant Account Info (Individual)
  let accountInfo = '';
  accountInfo += formatTag('00', data.bakongId);
  str += formatTag('29', accountInfo);

  str += formatTag('52', '5999'); // Category Code
  str += formatTag('53', data.currency === 'KHR' ? '116' : '840'); // Currency

  if (data.amount > 0) {
    const amountStr = data.currency === 'KHR' 
      ? Math.round(data.amount).toString() 
      : (data.amount % 1 === 0 ? data.amount.toString() : data.amount.toFixed(2));
    str += formatTag('54', amountStr);
  }

  str += formatTag('58', 'KH'); // Country
  str += formatTag('59', data.name || 'Merchant');
  str += formatTag('60', data.city || 'Phnom Penh');

  let addData = '';
  if (data.bill) addData += formatTag('01', data.bill);
  if (data.phone) addData += formatTag('02', data.phone);
  if (data.store) addData += formatTag('03', data.store);
  if (data.terminal) addData += formatTag('07', data.terminal);
  if (addData) str += formatTag('62', addData);

  str += '6304'; // CRC Tag
  str += calculateCRC(str);
  return str;
}

// --- Main App Component ---
export default function App() {
  // Load initial data from window (with robust mock Fallbacks if running standalone React)
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

  // React State
  const [products] = useState(initialProducts);
  const [customers] = useState(initialCustomers);
  const [settings] = useState(initialSettings);
  const [pendingOrders] = useState(initialPendingOrders);
  const [resumeOrder] = useState(initialResumeOrder);

  const [cart, setCart] = useState([]);
  const [selectedCustomerId, setSelectedCustomerId] = useState('');
  const [orderStatus, setOrderStatus] = useState('completed'); // completed = Sale, pending = Hold
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [darkMode, setDarkMode] = useState(false);

  // Modal / Sidebars state
  const [paymentModalOpen, setPaymentModalOpen] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [cashGiven, setCashGiven] = useState('');
  const [cardSimulating, setCardSimulating] = useState(false);
  const [cardProgress, setCardProgress] = useState(0);
  const [pendingOrdersOpen, setPendingOrdersOpen] = useState(false);
  const [analyticsViewOpen, setAnalyticsViewOpen] = useState(false);
  const [toast, setToast] = useState(null);

  // Clock state
  const [timeStr, setTimeStr] = useState(new Date().toLocaleTimeString());

  // Form submit ref
  const formRef = useRef(null);

  // Sync Tailwind class on html root element
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [darkMode]);

  // Sync Clock
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
      showToast('info', 'Held Order Resumed', `Continuing pending order #${resumeOrder.id}`);
    }
  }, [resumeOrder, products]);

  // Toast Helper
  const showToast = (type, title, message) => {
    setToast({ type, title, message });
    setTimeout(() => setToast(null), 4000);
  };

  // Cart operations
  const addToCart = (product) => {
    if (product.stock <= 0) {
      showToast('warning', 'Out of Stock', `${product.name} is currently unavailable.`);
      return;
    }
    const existing = cart.find(item => item.product.id === product.id);
    if (existing) {
      if (existing.quantity >= product.stock) {
        showToast('warning', 'Stock Limit Reached', `Only ${product.stock} units of ${product.name} are in stock.`);
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
        showToast('warning', 'Stock Limit Reached', `Only ${existing.product.stock} units available.`);
        return;
      }
      setCart(cart.map(item =>
        item.product.id === productId ? { ...item, quantity: nextQty } : item
      ));
    }
  };

  const clearCart = () => {
    if (cart.length === 0) return;
    if (window.confirm('Are you sure you want to clear your current cart?')) {
      setCart([]);
      showToast('info', 'Cart Cleared', 'All items have been removed.');
    }
  };

  // Quick Add First Match
  const handleQuickAdd = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const filtered = getFilteredProducts();
      if (filtered.length > 0) {
        const match = filtered[0];
        if (match.stock > 0) {
          addToCart(match);
          setSearchQuery('');
          showToast('success', 'Quick Added', `${match.name} added to cart.`);
        }
      }
    }
  };

  // Helpers
  const getSubtotal = () => cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
  const getGrandTotal = () => getSubtotal(); // Add tax/discounts if needed
  const getLowStockCount = () => products.filter(p => p.stock > 0 && p.stock <= 5).length;
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
      // If we are placing a hold order, submit directly
      submitCheckout();
      return;
    }

    if (paymentMethod === 'cash') {
      const total = getGrandTotal();
      const cashVal = parseFloat(cashGiven) || 0;
      if (cashVal < total) {
        showToast('error', 'Insufficient Cash', 'Amount received is less than total payable.');
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

    showToast('success', 'Transaction Successful', 'Processing transaction and loading receipt...');
    
    setTimeout(() => {
      if (formRef.current) {
        formRef.current.submit();
      }
    }, 1200);
  };

  // KHQR string generator
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

  // Analytics mockup metrics
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

  return (
    <div className={`min-h-screen font-sans transition-colors duration-350 ${darkMode ? 'bg-brand-bgDark text-slate-100' : 'bg-brand-bgLight text-slate-800'}`}>
      
      {/* Hidden legacy checkout form for PHP submission */}
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

      {/* Toast Notification */}
      {toast && (
        <div className="fixed right-6 top-6 z-50 flex items-center gap-3 rounded-2xl border border-white/20 bg-gradient-to-r from-blue-600 to-brand-secondary p-4 text-white shadow-2xl backdrop-blur-xl animate-fade-in">
          <Sparkles className="h-5 w-5 text-amber-300 animate-pulse" />
          <div>
            <div className="font-extrabold text-sm">{toast.title}</div>
            <div className="text-xs opacity-90">{toast.message}</div>
          </div>
        </div>
      )}

      {/* Viewport content container */}
      <div className="max-w-7xl mx-auto px-4 py-4 md:py-6 space-y-6">
        
        {/* Apple-level Minimalist Glass Header */}
        <header className="relative overflow-hidden rounded-[24px] border border-slate-200/50 dark:border-white/5 bg-white/60 dark:bg-slate-900/40 p-6 text-slate-850 dark:text-white shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl transition-all duration-300">
          <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-500/10 dark:bg-blue-500/5 blur-3xl"></div>
          <div className="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-purple-500/10 dark:bg-purple-500/5 blur-3xl"></div>
          
          <div className="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <div className="h-12 w-12 rounded-2xl bg-gradient-to-tr from-brand-primary to-brand-secondary flex items-center justify-center text-white shadow-lg shadow-blue-500/25">
                <Layers className="h-6 w-6" />
              </div>
              <div>
                <div className="text-[10px] font-extrabold uppercase tracking-[0.25em] text-slate-400 dark:text-slate-500">{settings.store_label}</div>
                <h1 className="text-xl md:text-2xl font-black tracking-tight mt-0.5 bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent">Mekong POS Terminal</h1>
              </div>
            </div>
            
            <div className="flex flex-wrap items-center gap-3">
              <button 
                onClick={() => setAnalyticsViewOpen(!analyticsViewOpen)} 
                className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold shadow-sm transition-all duration-300 active:scale-95 border border-slate-200/60 dark:border-white/10 ${
                  analyticsViewOpen 
                    ? 'bg-gradient-to-r from-brand-primary to-brand-secondary text-white border-transparent' 
                    : 'bg-white/80 dark:bg-slate-850/80 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
              >
                <BarChart2 className="h-4 w-4" />
                <span>{analyticsViewOpen ? 'លក់ទំនិញ Cashier Mode' : 'របាយការណ៍ Analytics'}</span>
              </button>

              <button 
                onClick={() => setPendingOrdersOpen(true)} 
                className="relative flex items-center gap-2 rounded-xl border border-slate-200/60 dark:border-white/10 bg-white/80 dark:bg-slate-850/80 hover:bg-slate-50 dark:hover:bg-slate-800 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 shadow-sm transition-all duration-300 active:scale-95"
              >
                <Clock className="h-4 w-4 text-brand-secondary" />
                <span>បញ្ជាទិញរង់ចាំ Holds</span>
                {pendingOrders.length > 0 && (
                  <span className="absolute -top-1.5 -right-1.5 bg-brand-danger text-white rounded-full text-[10px] font-bold px-2 py-0.5 animate-pulse">
                    {pendingOrders.length}
                  </span>
                )}
              </button>

              <button 
                onClick={() => setDarkMode(!darkMode)} 
                className="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/60 dark:border-white/10 bg-white/80 dark:bg-slate-850/80 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm active:scale-95"
              >
                {darkMode ? <Sun className="h-4 w-4 text-amber-400" /> : <Moon className="h-4 w-4 text-slate-500" />}
              </button>

              <div className="flex items-center gap-2 rounded-xl border border-slate-200/60 dark:border-white/10 bg-slate-900/5 dark:bg-slate-900/40 px-4 py-2.5 text-xs font-bold tracking-wider font-mono">
                <Clock className="h-4 w-4 text-brand-primary" />
                <span>{timeStr}</span>
              </div>
            </div>
          </div>
        </header>

        {/* Dashboard Metric summary widgets */}
        <section className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="p-5 rounded-2xl border bg-white dark:bg-brand-surfDark border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl">
            <div className="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Catalog Size</div>
            <div className="mt-2 text-2xl font-black tracking-tight">{products.length} Products</div>
            <div className="text-xs text-slate-400 mt-2 flex items-center gap-1"><LayoutGrid className="h-3.5 w-3.5" /> Total menu categories</div>
          </div>
          
          <div className="p-5 rounded-2xl border bg-white dark:bg-brand-surfDark border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl">
            <div className="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Subtotal Active</div>
            <div className="mt-2 text-2xl font-black text-brand-primary tracking-tight">${getSubtotal().toFixed(2)}</div>
            <div className="text-xs text-slate-400 mt-2 flex items-center gap-1"><ShoppingBag className="h-3.5 w-3.5" /> {cart.length} item lines</div>
          </div>
          
          <div className="p-5 rounded-2xl border bg-white dark:bg-brand-surfDark border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl">
            <div className="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Inventory Status</div>
            <div className={`mt-2 text-2xl font-black tracking-tight ${getLowStockCount() > 0 ? 'text-brand-danger' : 'text-brand-success'}`}>
              {getLowStockCount()} Low Stock
            </div>
            <div className="text-xs text-slate-400 mt-2 flex items-center gap-1"><AlertTriangle className="h-3.5 w-3.5" /> Items requiring restock</div>
          </div>
          
          <div className="p-5 rounded-2xl border bg-white dark:bg-brand-surfDark border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl">
            <div className="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Terminal Node</div>
            <div className="mt-2 text-2xl font-black text-brand-success tracking-tight flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-brand-success inline-block animate-ping"></span>
              <span>PP-01 Online</span>
            </div>
            <div className="text-xs text-slate-400 mt-2 flex items-center gap-1"><Activity className="h-3.5 w-3.5" /> Active workspace</div>
          </div>
        </section>

        {/* Dynamic content rendering */}
        {analyticsViewOpen ? (
          /* --- Redesigned Premium Analytics Dashboard --- */
          <div className="p-6 rounded-3xl border bg-white dark:bg-brand-surfDark border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl animate-fade-in space-y-6">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <TrendingUp className="h-6 w-6 text-brand-primary" />
                <h2 className="text-lg font-black tracking-tight">របាយការណ៍លក់ និងស្តុក (Analytics Overview)</h2>
              </div>
              <button 
                onClick={() => setAnalyticsViewOpen(false)} 
                className="text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
              >
                Close Report
              </button>
            </div>

            <div className="grid md:grid-cols-3 gap-6">
              
              {/* Category Sales Chart (2 cols) */}
              <div className="md:col-span-2 p-5 rounded-2xl border border-slate-200/50 dark:border-white/5 bg-slate-50/50 dark:bg-slate-900/30">
                <h3 className="text-xs font-bold text-slate-400 mb-4 uppercase tracking-[0.1em]">Sales by Category (USD)</h3>
                <div className="h-64">
                  <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={getCategorySalesData()}>
                      <XAxis dataKey="name" stroke="#888888" fontSize={11} tickLine={false} axisLine={false} />
                      <YAxis stroke="#888888" fontSize={11} tickLine={false} axisLine={false} />
                      <Tooltip 
                        contentStyle={{ 
                          background: 'rgba(15, 23, 42, 0.9)', 
                          border: '1px solid rgba(255,255,255,0.1)', 
                          borderRadius: '12px',
                          color: '#fff' 
                        }} 
                      />
                      <Bar dataKey="sales" fill="url(#bluePurpleGrad)" radius={[6, 6, 0, 0]}>
                        {/* Define linear gradient inside Recharts svg */}
                        <defs>
                          <linearGradient id="bluePurpleGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stopColor="#2563EB" />
                            <stop offset="100%" stopColor="#7C3AED" />
                          </linearGradient>
                        </defs>
                      </Bar>
                    </BarChart>
                  </ResponsiveContainer>
                </div>
              </div>

              {/* Stock Inventory levels (1 col) */}
              <div className="p-5 rounded-2xl border border-slate-200/50 dark:border-white/5 bg-slate-50/50 dark:bg-slate-900/30">
                <h3 className="text-xs font-bold text-slate-400 mb-4 uppercase tracking-[0.1em]">Current Stock Quantities</h3>
                <div className="h-64">
                  <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={getStockLevelsData()}>
                      <defs>
                        <linearGradient id="yellowGrad" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="0%" stopColor="#F59E0B" stopOpacity={0.4} />
                          <stop offset="100%" stopColor="#F59E0B" stopOpacity={0.0} />
                        </linearGradient>
                      </defs>
                      <XAxis dataKey="name" stroke="#888888" fontSize={9} tickLine={false} />
                      <Tooltip 
                        contentStyle={{ 
                          background: 'rgba(15, 23, 42, 0.9)', 
                          border: '1px solid rgba(255,255,255,0.1)', 
                          borderRadius: '12px',
                          color: '#fff' 
                        }} 
                      />
                      <Area type="monotone" dataKey="stock" stroke="#F59E0B" fill="url(#yellowGrad)" strokeWidth={2} />
                    </AreaChart>
                  </ResponsiveContainer>
                </div>
              </div>
            </div>
          </div>
        ) : (
          /* --- Cashier POS Terminal Redesigned --- */
          <div className="grid md:grid-cols-12 gap-6 items-start">
            
            {/* Products grid section */}
            <main className="md:col-span-8 space-y-6">
              
              {/* Filter controls */}
              <div className="flex flex-wrap items-center gap-3">
                
                {/* Search Menu Input */}
                <div className="relative flex-1 min-w-[240px]">
                  <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-brand-primary" />
                  <input 
                    type="text" 
                    placeholder="ស្វែងរកទំនិញ Search barcode, SKU, or item name..." 
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={handleQuickAdd}
                    className={`w-full py-3.5 pl-11 pr-4 text-sm font-semibold rounded-2xl border outline-none shadow-sm transition-all duration-300 focus:ring-4 ${
                      darkMode 
                        ? 'bg-slate-900 border-slate-800 text-white focus:border-brand-primary focus:ring-blue-950/30' 
                        : 'bg-white border-slate-200 focus:border-brand-primary focus:ring-blue-100/50'
                    }`}
                  />
                </div>

                {/* Category Pills Scroller */}
                <div className="flex items-center gap-2 overflow-x-auto py-1 pr-2 no-scrollbar">
                  {getCategories().map(cat => {
                    const isSelected = selectedCategory === cat || (cat === 'All' && selectedCategory === '');
                    return (
                      <button
                        key={cat}
                        onClick={() => setSelectedCategory(cat === 'All' ? '' : cat)}
                        className={`px-4 py-2 rounded-xl text-xs font-extrabold whitespace-nowrap border transition-all duration-300 ${
                          isSelected
                            ? 'bg-gradient-to-r from-brand-primary to-brand-secondary text-white border-transparent shadow-md'
                            : 'bg-white dark:bg-slate-900 border-slate-200/50 dark:border-white/5 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
                        }`}
                      >
                        {cat}
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* Products list grid */}
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                {getFilteredProducts().length === 0 ? (
                  <div className="col-span-full py-20 text-center rounded-2xl border border-dashed border-slate-200/80 dark:border-slate-800">
                    <LayoutGrid className="mx-auto h-12 w-12 text-slate-300 dark:text-slate-700 mb-3" />
                    <p className="text-sm font-bold text-slate-400">រកមិនឃើញទំនិញទេ (No items matched filter)</p>
                  </div>
                ) : (
                  getFilteredProducts().map(prod => {
                    const isOutOfStock = prod.stock <= 0;
                    const isLowStock = prod.stock > 0 && prod.stock <= 5;
                    const stockText = isOutOfStock ? 'អស់ស្តុក (Out of Stock)' : `${prod.stock} items`;
                    const hasInCart = cart.find(item => item.product.id === prod.id);

                    return (
                      <div 
                        key={prod.id}
                        onClick={() => addToCart(prod)}
                        className={`group relative flex flex-col overflow-hidden rounded-2xl border transition-all duration-300 bg-white dark:bg-slate-900/60 border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark hover:translate-y-[-4px] hover:border-brand-primary/40 ${
                          isOutOfStock ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        }`}
                      >
                        {/* Status Stock Badge */}
                        <span className={`absolute right-3 top-3 z-10 px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider border ${
                          isOutOfStock 
                            ? 'bg-rose-500/10 text-brand-danger border-brand-danger/20' 
                            : isLowStock 
                              ? 'bg-brand-warning/10 text-brand-warning border-brand-warning/20'
                              : 'bg-brand-success/10 text-brand-success border-brand-success/20'
                        }`}>
                          {stockText}
                        </span>

                        {/* Thumb Container */}
                        <div className={`aspect-square w-full relative flex items-center justify-center overflow-hidden bg-slate-50 dark:bg-slate-950/40 ring-1 ring-inset ${
                          darkMode ? 'ring-white/5' : 'ring-slate-100'
                        }`}>
                          {prod.image ? (
                            <img 
                              src={prod.image} 
                              alt={prod.name} 
                              className="h-full w-full object-cover transition-transform duration-350 group-hover:scale-105" 
                            />
                          ) : (
                            <LayoutGrid className="h-10 w-10 text-slate-200 dark:text-slate-800" />
                          )}
                          
                          {/* Selection indicator overlay */}
                          {hasInCart && (
                            <div className="absolute inset-0 bg-brand-primary/10 backdrop-blur-xs flex items-center justify-center">
                              <span className="bg-gradient-to-r from-brand-primary to-brand-secondary text-white rounded-full font-extrabold px-3 py-1 text-xs shadow-md">
                                {hasInCart.quantity}
                              </span>
                            </div>
                          )}
                        </div>

                        {/* Card Info Details */}
                        <div className="p-4 flex-1 flex flex-col justify-between space-y-2">
                          <h4 className="font-bold text-sm text-slate-800 dark:text-slate-200 truncate leading-snug">{prod.name}</h4>
                          <div className="flex items-center justify-between pt-1">
                            <span className="text-base font-black text-brand-success">${prod.price.toFixed(2)}</span>
                            <span className="text-[9px] font-bold uppercase tracking-wider text-slate-400 max-w-[80px] truncate">
                              {prod.sku || prod.category}
                            </span>
                          </div>
                        </div>
                      </div>
                    );
                  })
                )}
              </div>
            </main>

            {/* Shopping Cart Drawer panel */}
            <aside className="md:col-span-4 p-5 rounded-2xl border bg-white dark:bg-brand-surfDark border-slate-200/50 dark:border-white/5 shadow-premium-light dark:shadow-premium-dark backdrop-blur-xl sticky top-6 space-y-4">
              
              <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/5">
                <div className="flex items-center gap-2">
                  <ShoppingBag className="h-5 w-5 text-brand-primary" />
                  <h3 className="font-black text-sm">កន្ត្រកទំនិញ Active Cart</h3>
                </div>
                <span className="text-[10px] font-black bg-slate-100 dark:bg-slate-900 px-3 py-1 rounded-full text-slate-600 dark:text-slate-300">
                  {cart.reduce((sum, item) => sum + item.quantity, 0)} Items
                </span>
              </div>

              {/* Items scroll list */}
              <div className="space-y-3 min-h-[220px] max-h-[360px] overflow-y-auto pr-1">
                {cart.length === 0 ? (
                  <div className="h-[220px] flex flex-col items-center justify-center text-center space-y-1">
                    <ShoppingBag className="h-8 w-8 text-slate-300 dark:text-slate-800" />
                    <p className="text-xs font-bold text-slate-400">ទទេស្អាត (Cart is empty)</p>
                    <p className="text-[10px] text-slate-400">Select items to begin checkout</p>
                  </div>
                ) : (
                  cart.map(item => (
                    <div 
                      key={item.product.id}
                      className="flex items-center gap-3 p-3 rounded-xl border bg-slate-50/50 dark:bg-slate-950/20 border-slate-200/50 dark:border-white/5"
                    >
                      <div className="h-9 w-9 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-900 flex-shrink-0 flex items-center justify-center">
                        {item.product.image ? (
                          <img src={item.product.image} className="h-full w-full object-cover" />
                        ) : (
                          <LayoutGrid className="h-4 w-4 text-slate-400" />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{item.product.name}</div>
                        <div className="text-xs font-black text-brand-success mt-0.5">${item.product.price.toFixed(2)}</div>
                      </div>
                      
                      {/* Plus/minus selectors */}
                      <div className="flex items-center gap-1 rounded-full bg-white dark:bg-slate-900 p-0.5 border dark:border-white/5 shadow-sm">
                        <button 
                          onClick={() => updateCartQty(item.product.id, -1)}
                          className="h-6 w-6 rounded-full flex items-center justify-center hover:bg-brand-danger hover:text-white transition-all text-slate-500 text-xs"
                        >
                          <Minus className="h-3 w-3" />
                        </button>
                        <span className="w-4 text-center text-xs font-bold">{item.quantity}</span>
                        <button 
                          onClick={() => updateCartQty(item.product.id, 1)}
                          className="h-6 w-6 rounded-full flex items-center justify-center hover:bg-brand-success hover:text-white transition-all text-slate-500 text-xs"
                        >
                          <Plus className="h-3 w-3" />
                        </button>
                      </div>
                    </div>
                  ))
                )}
              </div>

              {/* CRM Loyalty selector */}
              <div className="space-y-3 pt-2">
                <div>
                  <label className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">ជ្រើសរើសអតិថិជន Customer</label>
                  <div className="relative">
                    <select 
                      value={selectedCustomerId}
                      onChange={(e) => setSelectedCustomerId(e.target.value)}
                      className={`w-full appearance-none py-2.5 pl-3 pr-10 text-xs font-bold rounded-xl border outline-none shadow-sm transition ${
                        darkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200'
                      }`}
                    >
                      <option value="">Walk-in Customer (អតិថិជនទូទៅ)</option>
                      {customers.map(c => (
                        <option key={c.id} value={c.id}>{c.name} {c.phone && `(${c.phone})`}</option>
                      ))}
                    </select>
                    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-450 pointer-events-none" />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Action Mode</label>
                    <select 
                      value={orderStatus} 
                      onChange={(e) => setOrderStatus(e.target.value)}
                      className={`w-full py-2.5 px-3 text-xs font-bold rounded-xl border outline-none shadow-sm transition ${
                        darkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200'
                      }`}
                    >
                      <option value="completed">Immediate Sale</option>
                      <option value="pending">Hold Order</option>
                    </select>
                  </div>
                  <div>
                    <label className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Receipt Output</label>
                    <div className="py-2.5 px-3 text-xs font-extrabold rounded-xl border border-brand-success/20 bg-brand-success/10 text-brand-success text-center">
                      Auto-Print
                    </div>
                  </div>
                </div>
              </div>

              {/* Receipt Total values breakdown */}
              <div className="p-4 rounded-xl border bg-slate-50/50 dark:bg-slate-950/20 border-slate-200/50 dark:border-white/5 space-y-2">
                <div className="flex justify-between items-center text-xs font-bold text-slate-400">
                  <span>Subtotal</span>
                  <span>${getSubtotal().toFixed(2)}</span>
                </div>
                <div className="flex justify-between items-center text-xs font-bold text-slate-400">
                  <span>Sales Tax (0%)</span>
                  <span>$0.00</span>
                </div>
                <div className="flex justify-between items-center border-t border-dashed border-slate-200 dark:border-white/5 pt-2.5 mt-2.5">
                  <span className="text-xs font-extrabold">Grand Total</span>
                  <span className="text-base font-black text-brand-success">${getGrandTotal().toFixed(2)}</span>
                </div>
              </div>

              {/* Checkout actions */}
              <div className="flex items-center gap-2 pt-2">
                <button 
                  onClick={clearCart}
                  title="Clear Cart"
                  className="h-11 w-11 rounded-xl flex items-center justify-center border border-brand-danger/20 bg-brand-danger/10 text-brand-danger hover:bg-brand-danger/20 transition-all shadow-sm"
                >
                  <Trash2 className="h-4.5 w-4.5" />
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
                  className="flex-1 h-11 rounded-xl bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-extrabold text-xs shadow-md hover:brightness-110 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1.5 transition-all duration-300"
                >
                  <span>{orderStatus === 'pending' ? 'Place Order on Hold' : 'ទូទាត់ប្រាក់ Process Checkout'}</span>
                  <ArrowRight className="h-4 w-4" />
                </button>
              </div>
            </aside>
          </div>
        )}
      </div>

      {/* --- Hold Orders Drawer (Modal style on right side) --- */}
      {pendingOrdersOpen && (
        <div className="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex justify-end animate-fade-in">
          <div className="w-full max-w-sm h-full bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-white/5 shadow-2xl flex flex-col p-6 space-y-4">
            <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/5">
              <h3 className="text-base font-extrabold flex items-center gap-2">
                <Clock className="h-5 w-5 text-brand-primary" />
                <span>Pending Hold Orders</span>
              </h3>
              <button 
                onClick={() => setPendingOrdersOpen(false)}
                className="h-9 w-9 rounded-xl flex items-center justify-center border border-slate-200 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-650"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto py-2 space-y-4">
              {pendingOrders.length === 0 ? (
                <div className="h-full flex flex-col items-center justify-center text-center space-y-2">
                  <Clock className="h-8 w-8 text-slate-300 dark:text-slate-800" />
                  <p className="text-xs font-bold text-slate-450">No pending orders found.</p>
                </div>
              ) : (
                pendingOrders.map(order => (
                  <div 
                    key={order.id}
                    onClick={() => {
                      window.location.href = `?resume=${order.id}`;
                    }}
                    className="p-4 rounded-xl border border-slate-200/50 dark:border-white/5 hover:border-brand-primary/40 cursor-pointer shadow-sm transition bg-slate-50/50 dark:bg-slate-900/30 group space-y-2"
                  >
                    <div className="flex justify-between items-start">
                      <div>
                        <span className="font-extrabold text-xs text-slate-850 dark:text-slate-200">Order #{order.id}</span>
                        <div className="text-[9px] font-bold text-slate-400 mt-1 flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          <span>{new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                      </div>
                      <span className="text-sm font-black text-brand-success">${parseFloat(order.total).toFixed(2)}</span>
                    </div>

                    {order.notes && (
                      <div className="p-2.5 bg-brand-warning/10 dark:bg-amber-950/10 rounded-lg text-[10px] border border-brand-warning/20">
                        <span className="font-extrabold block text-brand-warning uppercase tracking-wider">Note:</span>
                        <span className="text-slate-600 dark:text-slate-350 font-bold">{order.notes}</span>
                      </div>
                    )}

                    <div className="flex justify-between items-center border-t border-slate-100 dark:border-white/5 pt-2.5">
                      <span className="text-[10px] font-bold text-slate-450">{order.item_lines} items</span>
                      <span className="text-[10px] font-black uppercase tracking-wider text-brand-primary group-hover:text-brand-secondary flex items-center gap-1">
                        Resume <ArrowRight className="h-3 w-3" />
                      </span>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}

      {/* --- Advanced Checkout Dialog Modal --- */}
      {paymentModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 animate-fade-in">
          <div className={`w-full max-w-lg rounded-3xl border shadow-2xl p-6 relative transition bg-white dark:bg-slate-900 border-slate-200/50 dark:border-white/5 ${
            darkMode ? 'text-white' : 'text-slate-850'
          }`}>
            
            {/* Modal Header */}
            <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/5">
              <div className="flex items-center gap-3">
                <div className="h-10 w-10 rounded-xl bg-gradient-to-tr from-brand-primary to-brand-secondary text-white flex items-center justify-center shadow">
                  <CreditCard className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="font-black text-sm">ទូទាត់ប្រាក់ Checkout Processing</h3>
                  <p className="text-[10px] text-slate-400">Ensure security instruments are correctly routed</p>
                </div>
              </div>
              <button 
                onClick={() => setPaymentModalOpen(false)}
                className="h-8 w-8 rounded-xl flex items-center justify-center border border-slate-200 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-400"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Total display */}
            <div className="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950/30 border border-slate-200/50 dark:border-white/5 flex items-center justify-between">
              <div>
                <div className="text-[9px] font-extrabold uppercase tracking-widest text-slate-450">Total Payable</div>
                <div className="text-xl font-black text-brand-success mt-0.5">${getGrandTotal().toFixed(2)}</div>
              </div>
              <span className="text-[9px] font-black uppercase bg-brand-success/15 text-brand-success px-3 py-1 rounded-full tracking-wider border border-brand-success/20">
                USD Active
              </span>
            </div>

            {/* Instrument Selection Tab Row */}
            <div className="mt-5 space-y-2">
              <label className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block">Payment Instrument</label>
              <div className="grid grid-cols-3 gap-3">
                {settings.pos_method_cash_enabled === '1' && (
                  <button 
                    onClick={() => setPaymentMethod('cash')}
                    className={`p-3 rounded-2xl border text-center flex flex-col items-center justify-center gap-2 transition-all duration-300 ${
                      paymentMethod === 'cash' 
                        ? 'border-brand-primary bg-brand-primary/10 text-brand-primary font-extrabold shadow-sm' 
                        : 'border-slate-200/50 dark:border-white/5 text-slate-450 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-850'
                    }`}
                  >
                    <Wallet className="h-5 w-5" />
                    <span className="text-xs">Cash/សាច់ប្រាក់</span>
                  </button>
                )}
                {settings.pos_method_khqr_enabled === '1' && (
                  <button 
                    onClick={() => setPaymentMethod('khqr')}
                    className={`p-3 rounded-2xl border text-center flex flex-col items-center justify-center gap-2 transition-all duration-300 ${
                      paymentMethod === 'khqr' 
                        ? 'border-brand-primary bg-brand-primary/10 text-brand-primary font-extrabold shadow-sm' 
                        : 'border-slate-200/50 dark:border-white/5 text-slate-450 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-850'
                    }`}
                  >
                    <QrCode className="h-5 w-5" />
                    <span className="text-xs">Dynamic KHQR</span>
                  </button>
                )}
                {settings.pos_method_card_enabled === '1' && (
                  <button 
                    onClick={() => setPaymentMethod('card')}
                    className={`p-3 rounded-2xl border text-center flex flex-col items-center justify-center gap-2 transition-all duration-300 ${
                      paymentMethod === 'card' 
                        ? 'border-brand-primary bg-brand-primary/10 text-brand-primary font-extrabold shadow-sm' 
                        : 'border-slate-200/50 dark:border-white/5 text-slate-450 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-850'
                    }`}
                  >
                    <CreditCard className="h-5 w-5" />
                    <span className="text-xs">Card Reader</span>
                  </button>
                )}
              </div>
            </div>

            {/* Instrument-Specific Layout Area */}
            <div className="mt-5">
              {paymentMethod === 'cash' && (
                <div className="space-y-4">
                  <div>
                    <label className="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1.5">Cash Received</label>
                    <div className="relative">
                      <span className="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400">$</span>
                      <input 
                        type="number" 
                        step="0.01"
                        placeholder="0.00"
                        value={cashGiven}
                        onChange={(e) => setCashGiven(e.target.value)}
                        className={`w-full py-3.5 pl-8 pr-4 text-base font-black rounded-xl border outline-none transition focus:ring-4 ${
                          darkMode 
                            ? 'bg-slate-900 border-slate-800 text-white focus:border-brand-primary focus:ring-blue-950/30' 
                            : 'bg-slate-50 border-slate-200 focus:border-brand-primary focus:ring-blue-100/50'
                        }`}
                      />
                    </div>
                  </div>
                  
                  {parseFloat(cashGiven) > 0 && (
                    <div className="p-3.5 rounded-xl border border-brand-success/20 bg-brand-success/10 flex items-center justify-between">
                      <span className="text-xs font-bold text-brand-success uppercase tracking-wider">Balance Change (ប្រាក់អាប់)</span>
                      <span className="text-lg font-black text-brand-success">
                        ${Math.max(0, parseFloat(cashGiven) - getGrandTotal()).toFixed(2)}
                      </span>
                    </div>
                  )}
                </div>
              )}

              {paymentMethod === 'khqr' && (
                <div className="p-4 rounded-xl border border-slate-200/50 dark:border-white/5 bg-white text-center space-y-3">
                  <div className="inline-block p-3 rounded-xl border border-slate-100 bg-white">
                    <img 
                      src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(getKHQRString())}`} 
                      alt="Bakong KHQR Code"
                      className="h-40 w-40 mx-auto"
                    />
                  </div>
                  <div className="text-[10px] font-extrabold uppercase tracking-[0.25em] text-brand-danger animate-pulse">
                    Awaiting dynamic Bakong check approval...
                  </div>
                </div>
              )}

              {paymentMethod === 'card' && (
                <div className="p-6 rounded-xl border border-slate-200/50 dark:border-white/5 bg-slate-50/50 dark:bg-slate-950/30 text-center">
                  {cardSimulating ? (
                    <div className="space-y-4">
                      <div className="text-xs font-bold text-brand-primary">Connecting to card reader...</div>
                      <div className="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div 
                          className="bg-brand-primary h-full transition-all duration-300" 
                          style={{ width: `${cardProgress}%` }}
                        />
                      </div>
                      <div className="text-[10px] text-slate-400 font-bold">{cardProgress}% Handshake</div>
                    </div>
                  ) : (
                    <div className="space-y-2">
                      <CreditCard className="mx-auto h-8 w-8 text-brand-primary animate-bounce" />
                      <p className="text-xs font-bold text-slate-500">Insert/Tap credit card on POS reader device.</p>
                      <p className="text-[10px] text-slate-450">Submit transaction below to initialize wireless handshake.</p>
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* Modal actions */}
            <div className="mt-6 flex flex-col gap-2">
              <button 
                onClick={handleCheckoutSubmit}
                disabled={cardSimulating}
                className="w-full h-11 rounded-xl bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-extrabold text-xs shadow-md hover:brightness-110 disabled:opacity-55 flex items-center justify-center gap-1.5 transition-all"
              >
                <span>Confirm & Complete Transaction (ទូទាត់រួចរាល់)</span>
                <Check className="h-4 w-4" />
              </button>
              <button 
                onClick={() => setPaymentModalOpen(false)}
                disabled={cardSimulating}
                className="w-full py-2.5 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
              >
                Discard Checkout
              </button>
            </div>

          </div>
        </div>
      )}

    </div>
  );
}
