# Admin Panel Complete Documentation

## Overview
完整的 Laravel 后台管理系统，包含 CRUD 功能、用户管理、分析和统计。

## 功能模块

### 1. 仪表板 (Dashboard)
**路由**: `GET /admin`
- 总体统计（音乐会、用户数）
- 最近创建的音乐会
- 即将到来的音乐会列表

### 2. 音乐会管理 (Concerts Management)
**路由**: `GET /admin/concerts`
**功能**:
- ✅ **列表**: 完整音乐会管理表，支持：
  - 搜索（标题、艺术家、场地）
  - 按创建者筛选
  - 排序（日期、标题、创建时间）
  - 分页（每页 15 条）
  
- ✅ **查看**: 音乐会详细信息
  - 海报图片
  - 完整详情（艺术家、场地、日期、价格）
  - 创建者信息
  - 座位区域
  - 订单统计
  - 评论统计
  - 收藏人数
  - 最近订单列表
  - 最近评论列表

- ✅ **编辑**: 修改音乐会信息
  - 基本信息
  - 座位区域（JSON 数组）
  - 上传或更替图片
  - 自动验证和处理

- ✅ **删除**: 删除音乐会
  - 级联删除关联的订单和评论
  - 删除相关本地文件

### 3. 用户管理 (User Management)
**路由**: `GET /admin/users`
**功能**:
- 用户列表带统计：
  - 已创建的音乐会数
  - 已下单数
  - 已评论数
  - 收藏的音乐会数
- 搜索用户
- 查看用户详细信息：
  - 用户头像和基本信息
  - 管理员/已验证标签
  - 创建时间
  - 创建的音乐会列表
  - 最近订单
  - 收藏的音乐会

### 4. 分析 (Analytics)
**路由**: `GET /admin/analytics`
**统计内容**:
- 总体统计（音乐会、用户、订单、评论）
- 最热门音乐会（按订单数排序）
- 最多评价的音乐会
- 过去 12 个月的音乐会创建趋势
- 平均统计（每个用户音乐会数、每个音乐会评论数）

## 数据库结构

### 新增迁移

1. **add_seating_to_concerts_table** (2026_04_09_000001)
   - 添加 `seating_areas` JSON 列存储座位区域

2. **create_concert_user_table** (2026_04_09_000002)
   - 创建多对多关系表（用户-音乐会收藏）
   - 包含唯一约束防止重复收藏

3. **add_is_admin_to_users_table** (2026_04_09_000003)
   - 添加 `is_admin` 布尔字段（默认 false）

## 安全性

### 中间件
- `IsAdmin` 中间件检查用户是否有管理员权限
- 所有管理路由受 `auth`, `verified`, `is_admin` 中间件保护
- 未授权访问重定向到首页并显示错误信息

### 策略 (Policies)
- 音乐会删除受 `ConcertPolicy` 授权保护
- 确保只有创建者或管理员能删除音乐会

## 文件结构

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AdminController.php (新增)
│   ├── Middleware/
│   │   └── IsAdmin.php (新增)
│   └── Kernel.php (已更新)

database/
├── migrations/
│   ├── 2026_04_09_000001_add_seating_to_concerts_table.php
│   ├── 2026_04_09_000002_create_concert_user_table.php
│   └── 2026_04_09_000003_add_is_admin_to_users_table.php
└── seeders/
    └── AdminUserSeeder.php (新增)

resources/views/
├── layouts/
│   └── app.blade.php (已更新 - 添加管理员导航)
└── admin/
    ├── dashboard.blade.php (新增)
    ├── analytics.blade.php (新增)
    ├── concerts/
    │   ├── index.blade.php (新增)
    │   └── show.blade.php (新增)
    └── users/
        ├── index.blade.php (新增)
        └── show.blade.php (新增)

routes/
└── web.php (已更新 - 添加管理员路由)
```

## 路由列表

```
Admin Routes (Protected by: auth, verified, is_admin):

GET     /admin                          → admin.dashboard
GET     /admin/concerts                 → admin.concerts.index
GET     /admin/concerts/{concert}       → admin.concerts.show
DELETE  /admin/concerts/{concert}       → admin.concerts.delete
GET     /admin/users                    → admin.users.index
GET     /admin/users/{user}             → admin.users.show
GET     /admin/analytics                → admin.analytics
```

## 使用方式

### 1. 启用管理员账户

**方法 1: 使用 Tinker (推荐)**
```bash
php artisan tinker
>>> $user = \App\Models\User::first(); // 或指定用户
>>> $user->update(['is_admin' => true]);
>>> exit;
```

**方法 2: 使用 Seeder**
```bash
php artisan db:seed --class=AdminUserSeeder
```

**方法 3: 直接数据库查询**
```sql
UPDATE users SET is_admin = 1 WHERE id = 1;
```

### 2. 访问管理面板

1. 登录（需要已验证的账户）
2. 导航栏顶部会出现 **"Admin"** 链接（仅管理员可见）
3. 或直接访问: `http://127.0.0.1:8001/admin`

### 3. 管理功能

**创建音乐会**
- 访问 `/concerts/create`
- 填写表单包括座位区域
- 示例: "VIP Front, Regular, Balcony" (逗号分隔)

**编辑音乐会**
- 从列表或详情页点击 "Edit"
- 修改任何信息包括座位区域

**删除音乐会**
- 从列表点击 "Delete" 按钮
- 需要确认（防止误删）
- 自动删除关联数据和文件

**查看统计**
- 访问 Analytics 页面查看所有统计信息
- 热门音乐会排行
- 用户活动趋势

## API 响应示例

### 座位区域存储格式
```json
["VIP Front", "Regular", "Balcony"]
```

### 管理员用户标记
```php
user.is_admin // boolean: true 或 false
```

## 性能优化

- 使用 `with()` 加载关联数据避免 N+1 查询
- 分页处理大数据集
- 缓存统计数据（可选）

## 设计特色

- 玻璃态设计（Glassmorphism）
- 紫色渐变主题色 (#7c3aed)
- 响应式布局
- 平滑动画和过渡
- 详细的统计图表和表格

## 测试

所有现有测试通过 ✅
```bash
php artisan test
# 结果: 17 passed
```

## 快速启动

```bash
# 运行迁移
php artisan migrate --force

# 设置管理员
php artisan db:seed --class=AdminUserSeeder

# 启动服务器
php artisan serve --port=8001

# 访问
http://127.0.0.1:8001/admin
```

## 常见问题

**Q: 如何将用户设置为管理员？**
A: 使用 `php artisan tinker` 或直接更新数据库的 `is_admin` 字段

**Q: 管理员能做什么？**
A: 管理所有音乐会的 CRUD、管理用户信息、查看分析统计

**Q: 普通用户能访问管理面板吗？**
A: 不能，会被重定向到首页

**Q: 座位区域是必需的吗？**
A: 不是，是可选字段

**Q: 删除音乐会会发生什么？**
A: 级联删除关联的订单、评论、收藏，以及本地存储的图片文件

---

**版本**: 1.0
**最后更新**: 2026-04-09
**状态**: ✅ 完全功能
