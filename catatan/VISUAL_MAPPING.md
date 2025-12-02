# 🗺️ Visual Mapping: 17 Database Objects

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    DATABASE (warehouse_db)                              │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  FUNCTIONS (2) - Internal Use Only                                │  │
│  │  ┌─────────────────────────────────────────────────────────────┐  │  │
│  │  │ 1. GetNextSequentialID('BDG-U-', 'users')                   │  │  │
│  │  │    ↓ Called by all sp_Insert* procedures                    │  │  │
│  │  │ 2. fn_GetRegionFromUserId('JKT-U-000001') → 'JKT'          │  │  │
│  │  │    ↓ Extract region from distributed IDs                    │  │  │
│  │  └─────────────────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  STORED PROCEDURES (11)                                           │  │
│  │                                                                     │  │
│  │  ✅ IMPLEMENTED IN PHP (7)                                         │  │
│  │  ┌────────────────────────┐                                        │  │
│  │  │ 1. sp_InsertUser       │ ──→ User.php::create()                │  │
│  │  │ 2. sp_InsertWarehouse  │ ──→ Warehouse.php::create()           │  │
│  │  │ 3. sp_InsertWarehouseItem │ ──→ WarehouseItem.php::create()   │  │
│  │  │ 4. sp_InsertCartItem   │ ──→ Cart.php::add()                   │  │
│  │  │ 5. sp_InsertOrder      │ ──→ Order.php::create()               │  │
│  │  │ 6. sp_InsertOrderItem  │ ──→ Order.php::addOrderItem()         │  │
│  │  │ 7. sp_CheckoutFromCart_WithCursor │ ──→ Order.php::           │  │
│  │  │    (uses CURSOR + calls #5 & #6)  │     checkoutFromCart()    │  │
│  │  └────────────────────────┘                                        │  │
│  │                                                                     │  │
│  │  ⏳ READY TO USE (4)                                               │  │
│  │  ┌────────────────────────┐                                        │  │
│  │  │ 8. sp_GetUserOrders    │ ──→ Can replace Order::getByUser()    │  │
│  │  │ 9. sp_GetOrderDetail   │ ──→ Can replace findById()+getItems() │  │
│  │  │ 10. sp_GetCartByUser   │ ──→ Can replace Cart::getByUser()     │  │
│  │  └────────────────────────┘                                        │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  VIEWS (3) - Optimized JOINs                                      │  │
│  │                                                                     │  │
│  │  ⏳ READY TO USE (3)                                               │  │
│  │  ┌──────────────────────────┐                                      │  │
│  │  │ 1. v_UserOrdersSummary   │ ──→ Analytics: user spending stats  │  │
│  │  │    (aggregates)          │                                      │  │
│  │  │                          │                                      │  │
│  │  │ 2. v_WarehouseStockDetail│ ──→ Dashboard: stock monitoring     │  │
│  │  │    (warehouse+items+     │                                      │  │
│  │  │     products)            │                                      │  │
│  │  │                          │                                      │  │
│  │  │ 3. v_CartDetails         │ ──→ Used by sp_GetCartByUser        │  │
│  │  │    (cart+user+warehouse+ │     Can replace Cart::getByUser()   │  │
│  │  │     products)            │                                      │  │
│  │  └──────────────────────────┘                                      │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  TRIGGERS (1) - Auto-Active                                       │  │
│  │                                                                     │  │
│  │  ✅ ACTIVE (1)                                                     │  │
│  │  ┌─────────────────────────────────────────────────────────────┐  │  │
│  │  │ trg_OrderItems_AfterInsert_UpdateStock                      │  │  │
│  │  │                                                               │  │  │
│  │  │ ON: order_items (AFTER INSERT)                               │  │  │
│  │  │ ACTION:                                                       │  │  │
│  │  │   1. Check stock availability                                │  │  │
│  │  │   2. If insufficient → RAISERROR + ROLLBACK                  │  │  │
│  │  │   3. If OK → UPDATE warehouse_items (stock - qty)            │  │  │
│  │  │                                                               │  │  │
│  │  │ TRIGGERED BY:                                                 │  │  │
│  │  │   • sp_InsertOrderItem                                        │  │  │
│  │  │   • sp_CheckoutFromCart_WithCursor (internal)                │  │  │
│  │  └─────────────────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                         PHP APPLICATION                                  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  MODELS (5 files modified)                                        │  │
│  │                                                                     │  │
│  │  app/models/User.php                                               │  │
│  │    ├─ create() ──────────────→ sp_InsertUser                       │  │
│  │                                                                     │  │
│  │  app/models/Warehouse.php                                          │  │
│  │    ├─ create() ──────────────→ sp_InsertWarehouse                  │  │
│  │                                                                     │  │
│  │  app/models/WarehouseItem.php                                      │  │
│  │    ├─ create() ──────────────→ sp_InsertWarehouseItem              │  │
│  │                                                                     │  │
│  │  app/models/Cart.php                                               │  │
│  │    ├─ add() (new item) ──────→ sp_InsertCartItem                   │  │
│  │    └─ add() (existing) ──────→ UPDATE (PHP logic)                  │  │
│  │                                                                     │  │
│  │  app/models/Order.php                                              │  │
│  │    ├─ create() ──────────────→ sp_InsertOrder                      │  │
│  │    ├─ addOrderItem() ────────→ sp_InsertOrderItem                  │  │
│  │    │                              ↓                                 │  │
│  │    │                       (triggers stock reduction)               │  │
│  │    │                                                                │  │
│  │    └─ checkoutFromCart() ────→ sp_CheckoutFromCart_WithCursor ⭐   │  │
│  │         (NEW METHOD)              │                                 │  │
│  │                                   ├─→ Calculate total               │  │
│  │                                   ├─→ sp_InsertOrder                │  │
│  │                                   ├─→ CURSOR loop cart_items        │  │
│  │                                   ├─→ sp_InsertOrderItem (each)     │  │
│  │                                   │     ↓ trigger reduces stock     │  │
│  │                                   └─→ Clear cart                    │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  CONTROLLERS (1 file modified)                                    │  │
│  │                                                                     │  │
│  │  app/controllers/ClientController.php                              │  │
│  │    └─ checkout()                                                    │  │
│  │         │                                                           │  │
│  │         │  BEFORE: 30+ lines (manual loop)                         │  │
│  │         │  ├─ Get cart items                                        │  │
│  │         │  ├─ Loop: calculate total                                 │  │
│  │         │  ├─ Create order                                          │  │
│  │         │  ├─ Loop cart: insert items + reduce stock (manual)       │  │
│  │         │  └─ Clear cart                                            │  │
│  │         │                                                           │  │
│  │         │  AFTER: 1 line                                            │  │
│  │         └──→ $orderModel->checkoutFromCart($userId); ✅             │  │
│  │                                                                     │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                        FLOW DIAGRAM                                      │
│                                                                          │
│  USER REGISTRATION FLOW:                                                │
│  ┌────────┐    ┌─────────────┐    ┌─────────────┐    ┌──────────┐     │
│  │ Browser│───→│AuthController│───→│ User::create│───→│sp_Insert │     │
│  │  POST  │    │  ::register  │    │     ()      │    │   User   │     │
│  └────────┘    └─────────────┘    └─────────────┘    └────┬─────┘     │
│                                                             │           │
│                                                    ┌────────▼─────────┐ │
│                                                    │GetNextSequentialID│ │
│                                                    │  ('BDG-U-', ...)  │ │
│                                                    └────────┬─────────┘ │
│                                                             │           │
│                                                    ┌────────▼─────────┐ │
│                                                    │INSERT INTO users │ │
│                                                    │  user_id =       │ │
│                                                    │  'BDG-U-000001'  │ │
│                                                    └──────────────────┘ │
│                                                                          │
│  CHECKOUT FLOW (SIMPLIFIED):                                            │
│  ┌────────┐    ┌──────────────┐    ┌─────────────────┐                 │
│  │ User   │───→│ClientController│───→│Order::checkout  │                 │
│  │Checkout│    │  ::checkout   │    │  FromCart()     │                 │
│  └────────┘    └──────────────┘    └────────┬────────┘                 │
│                                               │                          │
│                                      ┌────────▼─────────────────────┐   │
│                                      │sp_CheckoutFromCart_WithCursor│   │
│                                      │                              │   │
│                                      │ 1. Calculate cart total      │   │
│                                      │ 2. sp_InsertOrder            │   │
│                                      │ 3. DECLARE CURSOR            │   │
│                                      │ 4. FETCH cart_items (loop)   │   │
│                                      │ 5.   sp_InsertOrderItem      │   │
│                                      │ 6.     ↓ TRIGGER fires       │   │
│                                      │ 7.       reduce stock        │   │
│                                      │ 8. DELETE cart_items         │   │
│                                      │ 9. COMMIT                    │   │
│                                      └──────────────────────────────┘   │
│                                                                          │
│  TRIGGER AUTO-FIRE:                                                     │
│  ┌──────────────────┐                                                   │
│  │ INSERT order_items│                                                   │
│  └────────┬─────────┘                                                   │
│           │                                                             │
│           ▼                                                             │
│  ┌───────────────────────────────────────────┐                         │
│  │ trg_OrderItems_AfterInsert_UpdateStock    │                         │
│  │                                            │                         │
│  │ IF stock < qty:                            │                         │
│  │   RAISERROR('Stok tidak cukup')            │                         │
│  │   ROLLBACK                                 │                         │
│  │ ELSE:                                      │                         │
│  │   UPDATE warehouse_items                   │                         │
│  │   SET stock = stock - qty                  │                         │
│  └────────────────────────────────────────────┘                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 📊 Quick Reference Table

| Object                                 | Type     | File Location     | Line | Status    |
| -------------------------------------- | -------- | ----------------- | ---- | --------- |
| sp_InsertUser                          | SP       | User.php          | 20   | ✅ Used   |
| sp_InsertWarehouse                     | SP       | Warehouse.php     | 20   | ✅ Used   |
| sp_InsertWarehouseItem                 | SP       | WarehouseItem.php | 20   | ✅ Used   |
| sp_InsertCartItem                      | SP       | Cart.php          | 37   | ✅ Used   |
| sp_InsertOrder                         | SP       | Order.php         | 21   | ✅ Used   |
| sp_InsertOrderItem                     | SP       | Order.php         | 44   | ✅ Used   |
| sp_CheckoutFromCart_WithCursor         | SP       | Order.php         | 210  | ✅ Used   |
| sp_GetUserOrders                       | SP       | -                 | -    | ⏳ Ready  |
| sp_GetOrderDetail                      | SP       | -                 | -    | ⏳ Ready  |
| sp_GetCartByUser                       | SP       | -                 | -    | ⏳ Ready  |
| GetNextSequentialID                    | Function | (Internal)        | -    | ✅ Used   |
| fn_GetRegionFromUserId                 | Function | (Internal)        | -    | ✅ Used   |
| v_UserOrdersSummary                    | View     | -                 | -    | ⏳ Ready  |
| v_WarehouseStockDetail                 | View     | -                 | -    | ⏳ Ready  |
| v_CartDetails                          | View     | -                 | -    | ⏳ Ready  |
| trg_OrderItems_AfterInsert_UpdateStock | Trigger  | (Auto)            | -    | ✅ Active |

**Total:** 16 objects (10 implemented, 6 ready to use)

---

**See also:**

- `MAPPING_DATABASE_OBJECTS.md` - Detailed mapping dengan code examples
- `STORED_PROCEDURES_GUIDE.md` - Complete implementation guide
- `02_logic_objects.sql` - Source SQL definitions
