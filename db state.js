let dumped = [
    {
        table_name: "password_resets",
        column_name: "email",
        data_type: "varchar",
    },
    {
        table_name: "password_resets",
        column_name: "token",
        data_type: "varchar",
    },
    {
        table_name: "password_resets",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "settings",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "settings",
        column_name: "key",
        data_type: "varchar",
    },
    {
        table_name: "settings",
        column_name: "value",
        data_type: "text",
    },
    {
        table_name: "settings",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "settings",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "customers",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "customers",
        column_name: "first_name",
        data_type: "varchar",
    },
    {
        table_name: "customers",
        column_name: "last_name",
        data_type: "varchar",
    },
    {
        table_name: "customers",
        column_name: "email",
        data_type: "varchar",
    },
    {
        table_name: "customers",
        column_name: "phone",
        data_type: "varchar",
    },
    {
        table_name: "customers",
        column_name: "address",
        data_type: "varchar",
    },
    {
        table_name: "customers",
        column_name: "avatar",
        data_type: "varchar",
    },
    {
        table_name: "customers",
        column_name: "user_id",
        data_type: "bigint",
    },
    {
        table_name: "customers",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "customers",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "shops",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "shops",
        column_name: "name",
        data_type: "varchar",
    },
    {
        table_name: "shops",
        column_name: "description",
        data_type: "text",
    },
    {
        table_name: "shops",
        column_name: "image",
        data_type: "varchar",
    },
    {
        table_name: "shops",
        column_name: "status",
        data_type: "tinyint",
    },
    {
        table_name: "shops",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "shops",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "migrations",
        column_name: "id",
        data_type: "int",
    },
    {
        table_name: "migrations",
        column_name: "migration",
        data_type: "varchar",
    },
    {
        table_name: "migrations",
        column_name: "batch",
        data_type: "int",
    },
    {
        table_name: "user_cart",
        column_name: "user_id",
        data_type: "bigint",
    },
    {
        table_name: "user_cart",
        column_name: "product_id",
        data_type: "bigint",
    },
    {
        table_name: "user_cart",
        column_name: "quantity",
        data_type: "int",
    },
    {
        table_name: "order_items",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "order_items",
        column_name: "price",
        data_type: "decimal",
    },
    {
        table_name: "order_items",
        column_name: "quantity",
        data_type: "int",
    },
    {
        table_name: "order_items",
        column_name: "order_id",
        data_type: "bigint",
    },
    {
        table_name: "order_items",
        column_name: "product_id",
        data_type: "bigint",
    },
    {
        table_name: "order_items",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "order_items",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "users",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "users",
        column_name: "first_name",
        data_type: "varchar",
    },
    {
        table_name: "users",
        column_name: "last_name",
        data_type: "varchar",
    },
    {
        table_name: "users",
        column_name: "email",
        data_type: "varchar",
    },
    {
        table_name: "users",
        column_name: "email_verified_at",
        data_type: "timestamp",
    },
    {
        table_name: "users",
        column_name: "password",
        data_type: "varchar",
    },
    {
        table_name: "users",
        column_name: "remember_token",
        data_type: "varchar",
    },
    {
        table_name: "users",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "users",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "tokenable_type",
        data_type: "varchar",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "tokenable_id",
        data_type: "bigint",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "name",
        data_type: "varchar",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "token",
        data_type: "varchar",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "abilities",
        data_type: "text",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "last_used_at",
        data_type: "timestamp",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "expires_at",
        data_type: "timestamp",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "personal_access_tokens",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "products",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "products",
        column_name: "name",
        data_type: "varchar",
    },
    {
        table_name: "products",
        column_name: "description",
        data_type: "text",
    },
    {
        table_name: "products",
        column_name: "category",
        data_type: "text",
    },
    {
        table_name: "products",
        column_name: "image",
        data_type: "varchar",
    },
    {
        table_name: "products",
        column_name: "barcode",
        data_type: "varchar",
    },
    {
        table_name: "products",
        column_name: "price",
        data_type: "decimal",
    },
    {
        table_name: "products",
        column_name: "quantity",
        data_type: "int",
    },
    {
        table_name: "products",
        column_name: "status",
        data_type: "tinyint",
    },
    {
        table_name: "products",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "products",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "orders",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "orders",
        column_name: "customer_id",
        data_type: "bigint",
    },
    {
        table_name: "orders",
        column_name: "user_id",
        data_type: "bigint",
    },
    {
        table_name: "orders",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "orders",
        column_name: "updated_at",
        data_type: "timestamp",
    },
    {
        table_name: "orders",
        column_name: "shop_id",
        data_type: "bigint",
    },
    {
        table_name: "failed_jobs",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "failed_jobs",
        column_name: "connection",
        data_type: "text",
    },
    {
        table_name: "failed_jobs",
        column_name: "queue",
        data_type: "text",
    },
    {
        table_name: "failed_jobs",
        column_name: "payload",
        data_type: "longtext",
    },
    {
        table_name: "failed_jobs",
        column_name: "exception",
        data_type: "longtext",
    },
    {
        table_name: "failed_jobs",
        column_name: "failed_at",
        data_type: "timestamp",
    },
    {
        table_name: "payments",
        column_name: "id",
        data_type: "bigint",
    },
    {
        table_name: "payments",
        column_name: "amount",
        data_type: "decimal",
    },
    {
        table_name: "payments",
        column_name: "order_id",
        data_type: "bigint",
    },
    {
        table_name: "payments",
        column_name: "user_id",
        data_type: "bigint",
    },
    {
        table_name: "payments",
        column_name: "created_at",
        data_type: "timestamp",
    },
    {
        table_name: "payments",
        column_name: "updated_at",
        data_type: "timestamp",
    },
];

const restructured = dumped.reduce((acc, obj) => {
    if (!acc[obj.table_name]) {
        acc[obj.table_name] = "";

        // {
        //     // table_name: obj.table_name,
        //     columns: "",
        // };
    }
    acc[obj.table_name] += obj.column_name + ",";
    // .push({
    //     name: obj.column_name,
    //     type: obj.data_type,
    // });
    return acc;
}, {});
console.log({ restructured });

let restructured_Result = {
    password_resets: "email,token,created_at,",
    settings: "id,key,value,created_at,updated_at,",
    customers:
        "id,first_name,last_name,email,phone,address,avatar,user_id,created_at,updated_at,",
    shops: "id,name,description,image,status,created_at,updated_at,",
    migrations: "id,migration,batch,",
    user_cart: "user_id,product_id,quantity,",
    order_items: "id,price,quantity,order_id,product_id,created_at,updated_at,",
    users: "id,first_name,last_name,email,email_verified_at,password,remember_token,created_at,updated_at,",
    personal_access_tokens:
        "id,tokenable_type,tokenable_id,name,token,abilities,last_used_at,expires_at,created_at,updated_at,",
    products:
        "id,name,description,category,image,barcode,price,quantity,status,created_at,updated_at,",
    orders: "id,customer_id,user_id,created_at,updated_at,shop_id,",
    failed_jobs: "id,connection,queue,payload,exception,failed_at,",
    payments: "id,amount,order_id,user_id,created_at,updated_at,",
};
