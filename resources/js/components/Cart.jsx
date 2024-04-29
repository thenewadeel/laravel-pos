import React, { Component } from "react";
import { createRoot } from "react-dom/client";
// import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

let axios = window.axios;
class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            cart: [],
            categories: [],
            customers: [],
            id: "",
            search: "",
            customer_id: "",
            translations: {},
            shops: [],
            shop_id: "",
            waiter_name: "",
            table_number: "",
            order_type: "dine-in",
            filterText: "",
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleOnChangeBarcode = this.handleOnChangeBarcode.bind(this);
        this.handleScanBarcode = this.handleScanBarcode.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadShops = this.loadShops.bind(this);
        this.loadCategories = this.loadCategories.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setCustomerId = this.setCustomerId.bind(this);
        this.setShopId = this.setShopId.bind(this);
        this.setWaiterName = this.setWaiterName.bind(this);
        this.setTableNumber = this.setTableNumber.bind(this);
        this.setOrderType = this.setOrderType.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.handleClickSave = this.handleClickSave.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        // this.loadCategories();
        this.loadCustomers();
        this.loadShops();
    }

    // load the transaltions for the react component
    loadTranslations() {
        axios
            .get("/locale/cart")
            .then((res) => {
                const translations = res.data;
                this.setState({ translations });
            })
            .catch((error) => {
                console.error("Error loading translations:", error);
            });
    }

    loadCustomers() {
        axios.get(`/listOfCustomers`).then((res) => {
            // console.log({res});
            const customers = res.data;
            this.setState({ customers });
        });
    }
    loadShops() {
        axios.get(`/listOfShops`).then((res) => {
            const shops = res.data;
            this.setState({ shops });
            this.setState({ shop_id: shops[0].id });
            // console.log({ shops });
            // console.log("shops returnd:", shops);
            const category_ids = shops
                .map((shop) => shop.categories.map((category) => category.id))
                .flat();
            this.loadCategories(category_ids);
        });
    }

    loadCategories(cat_ids = [1, 2], search = "") {
        // const query = (!!search ? `?search=${search}&` : "?") + "itemCount=300";
        // console.log({ cat_ids });
        const _cat_ids = cat_ids; // empty array
        axios
            .get(`/categories`, { params: { cat_ids: _cat_ids } })
            .then((res) => {
                // console.log({ res });
                const categories = res.data;
                this.setState({ categories });
                // console.log({ categories });
            });
    }

    handleOnChangeBarcode(event) {
        const id = event.target.value;
        // console.log(id);
        this.setState({ id });
    }

    loadCart() {
        axios.get("/cart").then((res) => {
            const cart = res.data;
            this.setState({ cart });
        });
    }

    handleScanBarcode(event) {
        event.preventDefault();
        const { id } = this.state;
        if (!!id) {
            axios
                .post("/cart", { id })
                .then((res) => {
                    this.loadCart();
                    this.setState({ id: "" });
                })
                .catch((err) => {
                    // Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }
    handleChangeQty(product_id, qty) {
        const cart = this.state.cart.map((c) => {
            if (c.id === product_id) {
                c.pivot.quantity = qty;
            }
            return c;
        });

        this.setState({ cart });
        if (!qty) return;

        axios
            .post("/cart/change-qty", { product_id, quantity: qty })
            .then((res) => {})
            .catch((err) => {
                // Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    getTotal(cart) {
        const total = cart.map((c) => c.pivot.quantity * c.price);
        return sum(total).toFixed(2);
    }
    handleClickDelete(product_id) {
        axios
            .post("/cart/delete", { product_id, _method: "DELETE" })
            .then((res) => {
                const cart = this.state.cart.filter((c) => c.id !== product_id);
                this.setState({ cart });
            });
    }
    handleEmptyCart() {
        axios.post("/cart/empty", { _method: "DELETE" }).then((res) => {
            this.setState({ cart: [] });
        });
    }
    handleChangeSearch(event) {
        const search = event.target.value;
        this.setState({ search });
    }
    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadCategories(event.target.value);
        }
    }

    addProductToCart(id, category_id, qty = 1) {
        let product = this.state.categories
            .find((c) => c.id === category_id)
            ?.products.find((p) => p.id === id);
        // let product = this.state.products.find((p) => p.id === id);
        if (!!product) {
            // if product is already in cart
            let cart = this.state.cart.find((c) => c.id === product.id);
            if (!!cart) {
                // update quantity
                this.setState({
                    cart: this.state.cart.map((c) => {
                        if (
                            c.id === product.id &&
                            product.quantity > c.pivot.quantity
                        ) {
                            c.pivot.quantity = c.pivot.quantity + qty;
                        }
                        return c;
                    }),
                });
            } else {
                if (product.quantity > 0) {
                    product = {
                        ...product,
                        pivot: {
                            quantity: qty,
                            product_id: product.id,
                            user_id: 1,
                        },
                    };

                    this.setState({ cart: [...this.state.cart, product] });
                }
            }

            axios
                .post("/cart", { id, quantity: qty }, { withCredentials: true })
                .then((res) => {
                    // this.loadCart();
                    // console.log(res);
                })
                .catch((err) => {
                    // Swal.fire("Error!", err.response.data.message, "error");
                });
        }
    }

    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }
    setShopId(event) {
        this.setState({ shop_id: event.target.value });

        const category_ids = this.state.shops
            .filter((shop) => shop.id === parseInt(event.target.value))
            .map((shop) => shop.categories.map((category) => category.id))
            .flat();
        // console.log({ category_ids });
        this.loadCategories(category_ids);

        // const category_ids = this.state.shops
        //     .find((s) => s.id === event.target.value)
        //     .categories.map((c) => c.id);

        // this.loadCategories(category_ids);
    }
    setWaiterName(event) {
        this.setState({ waiter_name: event.target.value });
    }
    setTableNumber(event) {
        this.setState({ table_number: event.target.value });
    }
    setOrderType(event) {
        this.setState({ order_type: event.target.value });
    }
    handleClickSave() {
        Swal.fire({
            title: "Saved Order",
            // input: "text",
            // inputValue: this.getTotal(this.state.cart),
            // cancelButtonText: this.state.translations["cancel_pay"],
            // showCancelButton: true,
            confirmButtonText: "OK",
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                let postObj = {
                    customer_id: this.state.customer_id,
                    shop_id: this.state.shop_id,
                    // amount,
                    table_number: this.state.table_number,
                    waiter_name: this.state.waiter_name,
                    order_type: this.state.order_type,
                };
                // console.log({postObj});
                return axios
                    .post("/orders", postObj)
                    .then((res) => {
                        this.loadCart();
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err.response.data.message);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                //
            }
        });
    }
    handleClickSubmit() {
        Swal.fire({
            title: this.state.translations["received_amount"],
            input: "text",
            inputValue: this.getTotal(this.state.cart),
            cancelButtonText: this.state.translations["cancel_pay"],
            showCancelButton: true,
            confirmButtonText: this.state.translations["confirm_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                let postObj = {
                    customer_id: this.state.customer_id,
                    shop_id: this.state.shop_id,
                    amount,
                    table_number: this.state.table_number,
                    waiter_name: this.state.waiter_name,
                    order_type: this.state.order_type,
                };
                // console.log({postObj});
                return axios
                    .post("/orders", postObj)
                    .then((res) => {
                        this.loadCart();
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err.response.data.message);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            let order = result.value.order;
            // console.log({ order });
            return axios
                .post(`/orders/${order.id}/addPayment`, {
                    order_id: order.id,
                    amount: 0,
                })
                .then((res) => {
                    this.loadCart();
                    return res.data;
                })
                .catch((err) => {
                    Swal.showValidationMessage(err);
                });
        });
    }
    render() {
        const { cart, categories, customers, id, translations, shops } =
            this.state;
        return (
            <div className="row">
                <div className="col-md-6 col-lg-4">
                    <div className="col ">
                        {/* <div className="col"> */}
                        <label htmlFor="shop-select">Department:</label>
                        <select
                            id="shop-select"
                            className="form-control"
                            onChange={this.setShopId}
                        >
                            {/* {console.log(shops)} */}
                            {shops.map((shp) => (
                                <option
                                    key={shp.id}
                                    value={shp.id}
                                >{`${shp.name}`}</option>
                            ))}
                        </select>
                        <div>
                            <select
                                className="form-control"
                                onChange={this.setOrderType}
                                id="order-type-select"
                                placeholder="Order Type"
                            >
                                {/* <option value="">{"order_type"}</option> */}
                                <option value="dine-in">{"dine-in"}</option>
                                <option value="take-away">{"take-away"}</option>
                                <option value="delivery">{"delivery"}</option>
                            </select>
                        </div>
                        {this.state.order_type === "delivery" && (
                            <div className="">
                                <label htmlFor="delivery-address">
                                    {"Delivery Address"}
                                </label>
                                <input
                                    type="text"
                                    className="form-control"
                                    id="delivery-address"
                                    // value={this.state.delivery_address}
                                    // onChange={this.setDeliveryAddress}
                                />
                            </div>
                        )}

                        {this.state.order_type === "dine-in" && (
                            <div className="">
                                <div className="">
                                    {/* <label htmlFor="waiter_name">{"waiter_name"}</label> */}
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="waiter_name"
                                        value={this.state.waiter_name}
                                        onChange={this.setWaiterName}
                                        placeholder="Waiter Name"
                                    />
                                </div>

                                <div className="">
                                    {/* <label htmlFor="table_number"> */}
                                    {/* {translations["table_number"]} */}
                                    {/* </label> */}
                                    <input
                                        type="text"
                                        className="form-control"
                                        id="table_number"
                                        value={this.state.table_number}
                                        onChange={this.setTableNumber}
                                        placeholder="Table Number"
                                    />
                                </div>
                            </div>
                        )}
                        <div className="input-group">
                            <input
                                type="text"
                                className="form-control"
                                placeholder={translations["general_customer"]}
                                value={this.state.customer_id}
                                onChange={(e) =>
                                    this.setState({
                                        customer_id: e.target.value,
                                    })
                                }
                            />
                            <div className="input-group-append">
                                <button
                                    className="btn btn-outline-secondary"
                                    type="button"
                                    onClick={this.handleSeach}
                                >
                                    <i className="fa fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <select
                            className="form-control mt-1"
                            value={this.state.customer_id || ""}
                            onChange={this.setCustomerId}
                        >
                            {customers
                                .filter((cus) =>
                                    cus.name
                                        .toLowerCase()
                                        .includes(
                                            this.state.search.toLowerCase()
                                        )
                                )
                                .map((cus) => (
                                    <option
                                        key={cus.id}
                                        value={cus.id}
                                    >{`${cus.id} - ${cus.name}`}</option>
                                ))}
                        </select>

                        {/* </div> */}
                    </div>
                    <div className="user-cart mx-3">
                        <div
                            className="card"
                            style={{ minHeight: "400px", overflowY: "scroll" }}
                        >
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{translations["product_name"]}</th>
                                        <th>{translations["quantity"]}</th>
                                        <th className="text-right">
                                            {translations["price"]}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cart.map((c) => (
                                        <tr key={c.id}>
                                            <td>{c.name}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-sm qty"
                                                    value={c.pivot.quantity}
                                                    onChange={(event) =>
                                                        this.handleChangeQty(
                                                            c.id,
                                                            event.target.value
                                                        )
                                                    }
                                                />
                                                <button
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() =>
                                                        this.handleClickDelete(
                                                            c.id
                                                        )
                                                    }
                                                >
                                                    <i className="fas fa-trash"></i>
                                                </button>
                                            </td>
                                            <td className="text-right">
                                                {window.APP.currency_symbol}{" "}
                                                {(
                                                    c.price * c.pivot.quantity
                                                ).toFixed(2)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="row  text-lg">
                        <div className="col">{translations["total"]}:</div>
                        <div className="col text-right">
                            {window.APP.currency_symbol} {this.getTotal(cart)}
                        </div>
                    </div>
                    <div className="col ">
                        <div className="col m-2">
                            <button
                                type="button"
                                className="btn btn-danger btn-block"
                                onClick={this.handleEmptyCart}
                                disabled={!cart.length}
                            >
                                {translations["cancel"]}
                            </button>
                        </div>
                        <div className="col m-2">
                            <button
                                type="button"
                                className="btn btn-info btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSave}
                            >
                                {"Save"}
                            </button>
                        </div>
                        <div className="col m-2">
                            <button
                                type="button"
                                className="btn btn-success btn-block"
                                disabled={!cart.length}
                                onClick={this.handleClickSubmit}
                            >
                                {"Pay"}
                            </button>
                        </div>
                    </div>
                </div>
                <div className="col-md-6 col-lg-8">
                    {/* <div className="">
                        <input
                            type="text"
                            className="form-control"
                            placeholder={translations["search_product"] + "..."}
                            onChange={this.handleChangeSearch}
                            onKeyDown={this.handleSeach}
                        />
                    </div> */}
                    <div
                        className="order-product"
                        style={{
                            overflow: "scroll",
                            height: "calc(80vh)",
                        }}
                    >
                        <nav className="nav">
                            <div
                                className="nav nav-tabs btn-group w-100 pb-2"
                                id="myTab"
                                role="tablist"
                            >
                                {categories.map((c) => (
                                    <a
                                        className={
                                            " btn btn-outline-secondary font-bold text-xl" +
                                            (c === categories[0]
                                                ? " active"
                                                : "")
                                        }
                                        id={"tab-header-" + c.id}
                                        data-toggle="tab"
                                        href={"#tab-" + c.id}
                                        role="tab"
                                        aria-controls={"tab-" + c.id}
                                        aria-selected={
                                            c === categories[0]
                                                ? "true"
                                                : "false"
                                        }
                                        key={c.id}
                                    >
                                        {c.name}
                                    </a>
                                ))}
                            </div>
                        </nav>
                        <div
                            className="tab-content "
                            id="myTabContent"
                            style={{
                                height: "100%",
                                borderRadius: "5px",
                                padding: "0.5rem",
                                overflow: "scroll",
                                // display: "flex",
                                // justifyContent: "space-around",
                            }}
                        >
                            {categories.map((c) => (
                                <div
                                    className={
                                        "tab-pane fade" +
                                        (c === categories[0]
                                            ? " show active"
                                            : "")
                                    }
                                    id={"tab-" + c.id}
                                    role="tabpanel"
                                    aria-labelledby={"tab-header-" + c.id}
                                    key={c.id}
                                >
                                    <div className="mb-2">
                                        <input
                                            type="text"
                                            placeholder="Filter Products..."
                                            className="form-control"
                                            value={this.state.filterText}
                                            onChange={(e) =>
                                                this.setState({
                                                    filterText: e.target.value,
                                                })
                                            }
                                        />
                                    </div>
                                    {c.products
                                        .filter((p) =>
                                            p.name
                                                .toLowerCase()
                                                .includes(
                                                    this.state.filterText.toLowerCase()
                                                )
                                        )
                                        .map((p) => (
                                            <div
                                                onClick={() =>
                                                    this.addProductToCart(
                                                        p.id,
                                                        c.id
                                                    )
                                                }
                                                key={p.id}
                                                className="item"
                                                style={{
                                                    border: "2px solid darkgray",
                                                    cursor: "pointer",
                                                    transition:
                                                        "box-shadow 0.3s",
                                                    "&:hover": {
                                                        border: "40px solid darkgray",
                                                    },
                                                    display: "flex",
                                                    height: "100%",
                                                    padding: "4px",
                                                    textAlign: "center",
                                                    flexGrow: 1,
                                                    alignItems: "center",
                                                    justifyContent:
                                                        "space-between",
                                                }}
                                            >
                                                <div
                                                    style={{
                                                        flexGrow: 1,
                                                        flexBasis: "0",
                                                    }}
                                                >
                                                    {p.name}
                                                    <br />
                                                    <span
                                                        style={{
                                                            fontStyle: "italic",
                                                        }}
                                                    >
                                                        ({p.price})
                                                    </span>
                                                    {/* <br /> */}
                                                    <input
                                                        style={{
                                                            width: "40px",
                                                            marginTop: "8px",
                                                        }}
                                                        type="number"
                                                        min={1}
                                                        defaultValue={1}
                                                        onKeyDown={(e) =>
                                                            e.key === "Enter" &&
                                                            this.addProductToCart(
                                                                p.id,
                                                                c.id,
                                                                parseInt(
                                                                    e.target
                                                                        .value,
                                                                    10
                                                                )
                                                            )
                                                        }
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

const root = document.getElementById("cart");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
