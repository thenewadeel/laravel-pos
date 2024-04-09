import React, { Component } from "react";
import { createRoot } from "react-dom/client";
import axios from "axios";
import Swal from "sweetalert2";
import { sum } from "lodash";

class Cart extends Component {
    constructor(props) {
        super(props);
        this.state = {
            id: "",
            search: "",
            customer_id: "",
            shop_id: "",
            cart: [],
            translations: {},
            products: [],
            shops: [],
            searchText: "",
        };

        this.loadCart = this.loadCart.bind(this);
        this.handleChangeQty = this.handleChangeQty.bind(this);
        this.handleEmptyCart = this.handleEmptyCart.bind(this);

        this.loadShops = this.loadShops.bind(this);
        this.loadProducts = this.loadProducts.bind(this);
        this.handleChangeSearch = this.handleChangeSearch.bind(this);
        this.handleSeach = this.handleSeach.bind(this);
        this.setShopId = this.setShopId.bind(this);
        this.handleClickSubmit = this.handleClickSubmit.bind(this);
        this.loadTranslations = this.loadTranslations.bind(this);
    }

    componentDidMount() {
        // load user cart
        this.loadTranslations();
        this.loadCart();
        this.loadProducts();
        // this.loadCustomers();
        this.loadShops();
        this.loadDiscounts();
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

    loadShops() {
        axios
            .get(`/listOfShops`)
            .then((res) => {
                const shops = res.data;
                this.setState({ shops: shops || [] });
                this.setState({ shop_id: shops?.[0]?.id || undefined });
            })
            .catch((err) => {
                console.error("Error loading shops:", err);
            });
    }

    loadDiscounts() {
        axios
            .get(`/listOfDiscounts`)
            .then((res) => {
                const discounts = res.data;
                this.setState({ discounts: discounts || [] });
                console.log({ discounts });
                // this.setState({ shop_id: shops?.[0]?.id || undefined });
            })
            .catch((err) => {
                console.error("Error loading discounts:", err);
            });
    }

    loadProducts(search = "") {
        const query = (!!search ? `?search=${search}&` : "?") + "itemCount=20";
        axios.get(`/productsbyCat`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
            console.log({ products });
        });
    }

    loadCart() {
        axios.get("/cart").then((res) => {
            const cart = res.data;
            this.setState({ cart });
        });
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
        console.log(event.target.value);
        const searchText = event.target.value;
        this.setState({ searchText });
    }
    handleSeach(event) {
        // if (event.keyCode === 13) {
        //     this.loadProducts(event.target.value);
        // }
    }

    addProductToCart(id) {
        let product = this.state.products.find((p) => p.id === id);
        if (!product) return; // return early if product not found

        const btn = document.getElementById(`add-to-cart-${id}`);
        if (btn) btn.disabled = true; // disable button

        let cart = this.state.cart.find((c) => c.id === product.id);
        if (cart) {
            // update quantity
            this.setState({
                cart: this.state.cart.map((c) => {
                    if (
                        c.id === product.id &&
                        product.quantity > c.pivot.quantity
                    ) {
                        c.pivot.quantity++;
                    }
                    return c;
                }),
            });
        } else {
            if (product.quantity > 0) {
                product = {
                    ...product,
                    pivot: {
                        quantity: 1,
                        product_id: product.id,
                        user_id: 1,
                    },
                };

                this.setState({ cart: [...this.state.cart, product] });
            }
        }

        axios
            .post("/cart", { id })
            .then((res) => {
                if (btn) btn.disabled = false; // enable button
            })
            .catch((err) => {
                if (btn) btn.disabled = false; // enable button
                window.location.reload();
                // Swal.fire("Error!", err.response.data.message, "error");
            });
    }

    setCustomerId(event) {
        this.setState({ customer_id: event.target.value });
    }
    setShopId(event) {
        this.setState({ shop_id: event.target.value });
    }

    showPopup(message) {
        Swal.fire({
            title: `C`,
            text: message,
        });
    }
    handleClickSubmit() {
        let cartTotal = this.getTotal(this.state.cart);
        // let paymentEntered = document.getElementById("paymentInput").value;
        // console.log({ paymentEntered });
        // if (paymentEntered < this.cartTotal) {
        //     alert("payment is less");
        //     return;
        // } else {
        Swal.fire({
            title: `Close Payment `,
            input: "text",
            inputValue: cartTotal,
            // text: "Do you want to pay & close",
            cancelButtonText: this.state.translations["cancel_pay"],
            showCancelButton: true,
            confirmButtonText: this.state.translations["confirm_pay"],
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                let postObj = {
                    customer_id: this.state.customer_id,
                    shop_id: this.state.shop_id,
                    amount: cartTotal,
                    order_type: "take-away",
                };
                return axios
                    .post("/orders", postObj)
                    .then((res) => {
                        this.loadCart();
                        // console.log({ res });

                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            // console.log({ result });
            if (result.isConfirmed) {
                const link = document.createElement("a");
                link.href = `/orders/printTokens/${result.value.order.id}`;
                link.download = "tokens.pdf";
                link.style.display = "none";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);

                console.log("posting to route, payment", result);
                let order = result.value.order;
                return axios
                    .post(`/orders/${order.id}/addPayment`, {
                        order_id: order.id,
                        // user_id: "XXXXX",
                        amount: 0,
                    })
                    .then((res) => {
                        this.loadCart();
                        // this.showPopup("QQQQQQQQ");
                        (async () => {
                            try {
                                await axios.get(`/orders/printPOS/${order.id}`);
                                console.log(`Sent to POS: ${order.id}`);
                            } catch (err) {
                                console.error(
                                    `Failed to send to POS: ${order.id}`,
                                    err
                                );
                            }
                        })(); // Run the async function immediately
                        return res.data;
                    })
                    .catch((err) => {
                        Swal.showValidationMessage(err);
                    });
            }
        });
        // }
    }
    render() {
        const { cart, products, customers, id, translations, shops } =
            this.state;
        return (
            <div className="row">
                {/* <pre style={{ maxWidth: "500px", overflowWrap: "break-word" }}>
                    {JSON.stringify(this.state, null, 4)}
                </pre> */}
                <div className="col col-8">
                    <div
                        className="card "
                        style={{
                            overflow: "scroll",
                            height: "calc(90vh)",
                            justifyContent: "space-between",
                        }}
                    >
                        <div className="card-header flex flex-row d-row align-middle items-center  p-0 m-0">
                            <div className="text-lg font-extrabold font-serif col-8">
                                Chand Raat Menu
                                {products.length} - {this.state.searchText}
                            </div>
                            <input
                                type="text"
                                className="form-control grow"
                                placeholder={
                                    translations["search_product"] + "..."
                                }
                                onChange={this.handleChangeSearch}
                                // onKeyDown={this.handleSeach}
                            />
                        </div>
                        <div
                            className="card-body order-product d-flex flex-wrap justify-content-between p-0"
                            style={{
                                justifyContent: "space-between",
                            }}
                        >
                            {products.map((p) => (
                                <div
                                    style={{
                                        display:
                                            this.state.searchText.trim() ===
                                                "" ||
                                            p.name
                                                .toLowerCase()
                                                .includes(
                                                    this.state.searchText.toLowerCase()
                                                )
                                                ? "block"
                                                : "none",
                                        border: "2px solid darkgray",
                                        cursor: "pointer",
                                        transition: "box-shadow 0.3s",
                                        "&:hover": {
                                            border: "40px solid darkgray",
                                        },
                                        width: "calc(20% - 4px)" /* 20px is the margin on each side */,
                                        margin: "2px",
                                    }}
                                    onClick={(e) => {
                                        if (!this.clickCooldown) {
                                            this.addProductToCart(p.id);
                                            this.clickCooldown = true;
                                            setTimeout(() => {
                                                this.clickCooldown = false;
                                            }, 500);
                                        }
                                    }}
                                    id={"add-to-cart-" + p.id}
                                    key={p.id}
                                    className={`text-lg font-serif font-extrabold d-flex align-items-center justify-content-center `}
                                >
                                    <div
                                        style={{
                                            textAlign: "center",
                                        }}
                                    >
                                        {p.name}
                                        <br />
                                        {p.description}
                                        <span style={{ fontStyle: "italic" }}>
                                            ({p.price})
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="card-footer">DSA</div>
                    </div>
                </div>
                <div
                    className="col-4 card container-fluid "
                    style={{ height: "90vh" }}
                >
                    <div className="col card-body">
                        <div className="col " style={{ height: "60vh" }}>
                            {/* <select
                                className="form-control"
                                onChange={this.setShopId}
                            >
                                {shops.map((shp) => (
                                    <option
                                        key={shp.id}
                                        value={shp.id}
                                    >{`${shp.name}`}</option>
                                ))}
                            </select>
                            <div className="input-group">
                                <p className="form-control-plaintext">
                                    {translations["general_customer"]}
                                </p>
                            </div> */}
                            <div
                                className="user-cart row w-100"
                                style={{ height: "100%", overflowY: "scroll" }}
                            >
                                {/* <div
                                className="card"
                            > */}
                                <table className="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                {translations["product_name"]}
                                            </th>
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
                                                                event.target
                                                                    .value
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
                                                        c.price *
                                                        c.pivot.quantity
                                                    ).toFixed(2)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                {/* </div> */}
                            </div>
                        </div>
                    </div>
                    <div
                        className="card-footer"
                        style={{
                            overflow: "scroll",
                            border: "2px solid darkgray",
                            // height: "calc(30vh)",
                            justifyContent: "space-between",
                        }}
                    >
                        <div className="card-header flex flex-row font-bold text-lg">
                            Change Calculator
                        </div>
                        <div className="card-body order-product w-100 font-serif font-bold text-lg">
                            {/* Amount Total: ${this.getTotal(cart).toFixed(2)} */}
                            <div className=" ">
                                <div className="d-flex justify-content-between col-12 ">
                                    <span className="">Total Amount</span>
                                    <span className=" ">
                                        {window.APP.currency_symbol}{" "}
                                        {this.getTotal(cart)}
                                    </span>
                                </div>
                            </div>
                            <div className="input-group py-0 my-0">
                                {/* <div className="input-group-prepend"> */}
                                <span className=" col-6 ">Payment</span>
                                {/* </div> */}
                                <input
                                    type="number"
                                    className="form-control text-right"
                                    aria-label="Amount (to the nearest dollar)"
                                    id="paymentInput"
                                    onChange={(e) => {
                                        const inputValue = parseInt(
                                            e.target.value
                                        );
                                        document.getElementById(
                                            "change"
                                        ).innerHTML =
                                            this.getTotal(cart) - inputValue;
                                    }}
                                />
                            </div>
                            <div className="input-group ">
                                <div className="d-flex justify-content-between col-12 ">
                                    <span className="">Change</span>
                                    <span className=" ">
                                        {window.APP.currency_symbol}{" "}
                                        <span id="change">---</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div className="card-footer">
                            <div className="row ">
                                <div className="col">
                                    <button
                                        type="button"
                                        className="btn btn-danger btn-block"
                                        onClick={this.handleEmptyCart}
                                        disabled={!cart.length}
                                    >
                                        {translations["cancel"]}
                                    </button>
                                </div>

                                <div className="col ">
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
                    </div>
                </div>
            </div>
        );
    }
}

export default Cart;

const root = document.getElementById("cart-tokens");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
