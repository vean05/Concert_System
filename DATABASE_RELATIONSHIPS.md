# Concert System - 完整数据库ERD与关系图

## Mermaid ERD 完整代码

```mermaid
erDiagram
    USERS ||--o{ CONCERTS : "ONE TO MANY: 创建"
    USERS ||--o{ ORDERS : "ONE TO MANY: 下单"
    USERS ||--o{ REVIEWS : "ONE TO MANY: 评价"
    USERS ||--o{ CONCERT_USER : "ONE TO MANY: 收藏"
    USERS ||--o{ PAYMENT_CARDS : "ONE TO MANY: 拥有"
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "ONE TO MANY: 生成"
    CONCERTS ||--o{ ORDERS : "ONE TO MANY: 被订购"
    CONCERTS ||--o{ REVIEWS : "ONE TO MANY: 被评价"
    CONCERTS ||--o{ CONCERT_USER : "ONE TO MANY: 被收藏"

    USERS {
        bigint id PK "自增主键"
        string name "用户名"
        string email UK "邮箱（唯一）"
        timestamp email_verified_at "邮箱验证时间"
        string password "加密密码"
        string remember_token "记住我令牌"
        boolean is_admin "是否管理员"
        string role "角色（admin/user）"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }

    CONCERTS {
        bigint id PK "自增主键"
        string title "演唱会名称"
        string artist "艺术家/歌手"
        string venue "地点"
        date date "演唱会日期"
        text description "描述"
        decimal ticket_price "票价"
        int total_ticket "总票数"
        string image "海报图片"
        int seating "座位数量"
        bigint created_by FK "创建者ID"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }

    ORDERS {
        bigint id PK "自增主键"
        bigint user_id FK "用户ID"
        bigint concert_id FK "演唱会ID"
        int quantity "购买数量"
        decimal total_price "总价格"
        enum status "订单状态（confirmed/cancelled）"
        string payment_method "支付方式"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }

    REVIEWS {
        bigint id PK "自增主键"
        bigint user_id FK "用户ID"
        bigint concert_id FK "演唱会ID"
        int rating "评分（1-5）"
        text comment "评论内容"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }

    CONCERT_USER {
        bigint id PK "自增主键"
        bigint user_id FK "用户ID"
        bigint concert_id FK "演唱会ID"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }

    PAYMENT_CARDS {
        bigint id PK "自增主键"
        bigint user_id FK "用户ID"
        string card_type "卡类型（visa/master）"
        text card_number "加密卡号"
        string expiry_date "过期日期（MM/YY）"
        text cvv "加密CVV"
        string full_name "持卡人姓名"
        string country "国家"
        text address "地址"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }

    PASSWORD_RESETS {
        string email PK "邮箱"
        string token "重置令牌"
        timestamp created_at "创建时间"
    }

    PERSONAL_ACCESS_TOKENS {
        bigint id PK "自增主键"
        string tokenable_type "关联模型类型"
        bigint tokenable_id "关联模型ID"
        string name "令牌名称"
        string token UK "令牌（唯一）"
        text abilities "权限列表"
        timestamp last_used_at "最后使用时间"
        timestamp created_at "创建时间"
        timestamp updated_at "更新时间"
    }
```

## 关系说明表

### 一对多（ONE TO MANY）关系

| 关系ID | 主表 | 从表 | 外键字段 | 关系类型 | 说明 |
|--------|------|------|---------|---------|------|
| 1 | USERS | CONCERTS | created_by | **1:N** | 一个用户可以创建多个演唱会 |
| 2 | USERS | ORDERS | user_id | **1:N** | 一个用户可以下多个订单 |
| 3 | USERS | REVIEWS | user_id | **1:N** | 一个用户可以写多个评论 |
| 4 | USERS | CONCERT_USER | user_id | **1:N** | 一个用户可以收藏多个演唱会 |
| 5 | USERS | PAYMENT_CARDS | user_id | **1:N** | 一个用户可以保存多张支付卡 |
| 6 | USERS | PERSONAL_ACCESS_TOKENS | tokenable_id | **1:N** | 一个用户可以生成多个API令牌 |
| 7 | CONCERTS | ORDERS | concert_id | **1:N** | 一个演唱会可以被多个用户订购 |
| 8 | CONCERTS | REVIEWS | concert_id | **1:N** | 一个演唱会可以收到多个评论 |
| 9 | CONCERTS | CONCERT_USER | concert_id | **1:N** | 一个演唱会可以被多个用户收藏 |

### 多对多（MANY TO MANY）关系

| 关系 | 主表 | 从表 | 联接表 | 说明 |
|------|------|------|--------|------|
| **M:N** | USERS | CONCERTS | CONCERT_USER | 用户-演唱会收藏 |

**CONCERT_USER 约束：**
- `UNIQUE KEY (user_id, concert_id)` - 确保用户只能收藏同一演唱会一次
- 删除级联：用户或演唱会删除时，对应收藏记录自动删除

### 特殊关系

| 表 | 特殊说明 |
|----|---------|
| **REVIEWS** | `UNIQUE KEY (user_id, concert_id)` - 用户对同一演唱会只能评价一次 |
| **PAYMENT_CARDS** | 存储加密的支付卡信息，用户可以保存多张卡 |
| **PASSWORD_RESETS** | 临时表，用于密码重置流程 |
| **PERSONAL_ACCESS_TOKENS** | Sanctum授权表，用于API认证 |

## 数据库索引

### 主键与唯一键
```sql
-- 主键 (PRIMARY KEY)
USERS.id, CONCERTS.id, ORDERS.id, REVIEWS.id, 
CONCERT_USER.id, PAYMENT_CARDS.id, PERSONAL_ACCESS_TOKENS.id

-- 唯一键 (UNIQUE)
USERS.email
REVIEWS.user_id + concert_id
CONCERT_USER.user_id + concert_id
PERSONAL_ACCESS_TOKENS.token
PASSWORD_RESETS.email
```

### 外键索引 (FOREIGN KEY)
```sql
CONCERTS.created_by → USERS.id
ORDERS.user_id → USERS.id
ORDERS.concert_id → CONCERTS.id
REVIEWS.user_id → USERS.id
REVIEWS.concert_id → CONCERTS.id
CONCERT_USER.user_id → USERS.id
CONCERT_USER.concert_id → CONCERTS.id
PAYMENT_CARDS.user_id → USERS.id
PERSONAL_ACCESS_TOKENS.tokenable_id → USERS.id
```

### 查询索引
```sql
-- 性能优化的普通索引
USERS: idx_email, idx_is_admin, idx_role
CONCERTS: idx_artist, idx_venue, idx_date, idx_created_by
ORDERS: idx_user_id, idx_concert_id, idx_status, idx_created_at
REVIEWS: idx_user_id, idx_concert_id, idx_rating
CONCERT_USER: idx_user_id, idx_concert_id
PAYMENT_CARDS: idx_user_id, idx_card_type
PERSONAL_ACCESS_TOKENS: idx_tokenable_type_id, idx_token
```

## 级联删除规则

所有外键都配置了 `ON DELETE CASCADE`，含义：
- 删除用户 → 自动删除该用户的所有订单、评论、收藏、支付卡和令牌
- 删除演唱会 → 自动删除该演唱会的所有订单、评论和收藏

## 数据流向示例

### 用户购票流程
```
1. 用户登录 (USERS 表)
2. 浏览演唱会 (CONCERTS 表)
3. 创建订单 (ORDERS 表) - 关联 user_id 和 concert_id
4. 选择支付卡 (PAYMENT_CARDS 表)
5. 订单状态更新为 'confirmed'
```

### 用户评价流程
```
1. 用户登录 (USERS 表)
2. 查看已购票的演唱会 (通过 ORDERS 表)
3. 撰写评论 (REVIEWS 表) - 关联 user_id 和 concert_id
4. 同时更新演唱会评分统计
```

### 用户收藏流程
```
1. 用户登录 (USERS 表)
2. 浏览演唱会 (CONCERTS 表)
3. 收藏演唱会 (CONCERT_USER 表) - 插入 user_id 和 concert_id
4. 已收藏演唱会在用户个人页面展示
```
