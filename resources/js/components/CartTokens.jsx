import React, { Component } from "react";
import { createRoot } from "react-dom";
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
        axios.get(`/listOfShops`).then((res) => {
            const shops = res.data;
            this.setState({ shops });
            this.setState({ shop_id: shops[0].id });
            // console.log("shops returnd:", shops);
        });
    }

    loadProducts(search = "") {
        const query = (!!search ? `?search=${search}&` : "?") + "itemCount=20";
        axios.get(`/productsbyCat`).then((res) => {
            const products = res.data.data;
            this.setState({ products });
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
        const search = event.target.value;
        this.setState({ search });
    }
    handleSeach(event) {
        if (event.keyCode === 13) {
            this.loadProducts(event.target.value);
        }
    }

    addProductToCart(id) {
        let product = this.state.products.find((p) => p.id === id);
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
                            c.pivot.quantity = c.pivot.quantity + 1;
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
    }

    showPopup(message) {
        Swal.fire({
            title: `C`,
            text: message,
        });
    }
    handleClickSubmit() {
        let cartTotal = this.getTotal(this.state.cart);
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
                        // this.showPopup("QQQQQQQQ");
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

                // Swal.fire({
                //     title: "Paid!".amount,
                //     text: `Your order has been closed. Cash Served: ${JSON.stringify(
                //         result.value.order
                //     )}`,
                //     icon: "success",
                // });
            }
        });
    }
    render() {
        const { cart, products, customers, id, translations, shops } =
            this.state;
        return (
            <div className="col">
                {/* <pre style={{ maxWidth: "500px", overflowWrap: "break-word" }}>
                    {JSON.stringify(this.state, null, 4)}
                </pre> */}
                <div
                    className="card "
                    style={{
                        overflow: "scroll",
                        height: "calc(30vh)",
                        justifyContent: "space-between",
                    }}
                >
                    <div className="card-header flex flex-row d-row align-middle items-center">
                        <div className="text-lg font-extrabold font-serif col-8">
                            Chand Raat Menu
                        </div>
                        <input
                            type="text"
                            className="form-control grow"
                            placeholder={translations["search_product"] + "..."}
                            onChange={this.handleChangeSearch}
                            onKeyDown={this.handleSeach}
                        />
                    </div>
                    <div className="card-body order-product">
                        {products.map((p) => (
                            <div
                                onClick={() => this.addProductToCart(p.id)}
                                key={p.id}
                                className="item"
                                style={{
                                    border: "2px solid darkgray",
                                    cursor: "pointer",
                                    transition: "box-shadow 0.3s",
                                    "&:hover": {
                                        border: "40px solid darkgray",
                                    },
                                }}
                            >
                                {/* {console.log({"p":p.image_url})} */}
                                {/* <img
                                    src={
                                        p.image_url === "/storage/"
                                            ? "/images/defaultItem.png"
                                            : p.image_url
                                    }
                                    alt="" className="w-64 h-64 border-4 border-red-900"
                                /> */}
                                <div
                                    style={{
                                        padding: "4px",
                                        textAlign: "center",
                                    }}
                                >
                                    {p.name}
                                    <br />
                                    <span style={{ fontStyle: "italic" }}>
                                        ({p.price})
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                    <div className="card-footer">DSA</div>
                </div>
                <div className="col-md-6 col-lg-4">
                    <div className="col ">
                        {/* <div className="col"> */}
                        <select
                            className="form-control"
                            onChange={this.setShopId}
                        >
                            {/* {console.log(shops)} */}
                            {/* <option value="">Shops</option> */}
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
                        </div>
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
        );
    }
}

export default Cart;

const root = document.getElementById("cart-tokens");
if (root) {
    const rootInstance = createRoot(root);
    rootInstance.render(<Cart />);
}
