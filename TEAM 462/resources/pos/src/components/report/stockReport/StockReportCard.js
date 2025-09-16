import React, { useEffect, useState } from "react";
import MasterLayout from "../../MasterLayout";
import TabTitle from "../../../shared/tab-title/TabTitle";
import {
    currencySymbolHandling,
    getFormattedMessage,
    placeholderText,
} from "../../../shared/sharedMethod";
import ReactSelect from "../../../shared/select/reactSelect";
import { connect } from "react-redux";
import { fetchAllWarehouses } from "../../../store/action/warehouseAction";
import { fetchFrontSetting } from "../../../store/action/frontSettingAction";
import { fetchAllProducts } from "../../../store/action/productAction";
import TopProgressBar from "../../../shared/components/loaders/TopProgressBar";
import StockReportTable from "./StockReportTable";
import { Button, Card, CardBody, CardHeader, Col, Row } from "reactstrap";
import DateRangePicker from "../../../shared/datepicker/DateRangePicker";
import { toast } from "react-toastify";
import axiosApi from "../../../config/apiConfig";
import moment from "moment";
import { dateFormat } from "../../../constants";

const StockReportCard = (props) => {
    const {
        isLoading,
        fetchAllWarehouses,
        fetchFrontSetting,
        fetchAllProducts,
        frontSetting,
        warehouses,
        products,
        allConfigData,
    } = props;

    const [selectedProduct, setSelectedProduct] = useState(null);
    const [selectedWarehouse, setSelectedWarehouse] = useState(null);
    const [dateRange, setDateRange] = useState({
        start_date: moment().startOf('month').format(dateFormat.NATIVE),
        end_date: moment().endOf('month').format(dateFormat.NATIVE)
    });
    const [stockReportData, setStockReportData] = useState(null);
    const [isGenerating, setIsGenerating] = useState(false);

    const currencySymbol =
        frontSetting &&
        frontSetting.value &&
        frontSetting.value.currency_symbol;

    const [filteredProducts, setFilteredProducts] = useState([]);

    const onDateSelector = (dateParams) => {
        if (dateParams && dateParams.params) {
            setDateRange({
                start_date: dateParams.params.start_date,
                end_date: dateParams.params.end_date
            });
        }
    };

    const fetchProductsByWarehouse = async (warehouseId) => {
        try {
            const response = await axiosApi.get(`stock-report/products?warehouse_id=${warehouseId}`);
            setFilteredProducts(response.data.data);
        } catch (error) {
            console.error("Error fetching products by warehouse:", error);
            setFilteredProducts([]);
        }
    };

    useEffect(() => {
        fetchAllWarehouses();
        fetchAllProducts();
        fetchFrontSetting();
    }, []);

    // Réinitialiser le produit sélectionné et récupérer les produits quand l'entrepôt change
    useEffect(() => {
        if (selectedWarehouse) {
            setSelectedProduct(null);
            fetchProductsByWarehouse(selectedWarehouse.value);
        } else {
            setFilteredProducts([]);
        }
    }, [selectedWarehouse]);

    // Préparer les données pour les sélecteurs
    const warehouseOptions = warehouses?.map(warehouse => ({
        label: warehouse.attributes?.name || warehouse.name,
        value: warehouse.id,
    })) || [];

    // Utiliser les produits filtrés par entrepôt
    const productOptions = filteredProducts?.map(product => ({
        label: `${product.name} (${product.code})`,
        value: product.id,
    })) || [];

    const handleGenerateReport = async () => {
        if (!selectedProduct || !selectedWarehouse) {
            toast.error("Veuillez sélectionner un produit et un entrepôt");
            return;
        }

        if (moment(dateRange.start_date).isAfter(moment(dateRange.end_date))) {
            toast.error("La date de début doit être antérieure à la date de fin");
            return;
        }

        setIsGenerating(true);
        try {
            const response = await axiosApi.post('stock-report/generate', {
                product_id: selectedProduct.value,
                warehouse_id: selectedWarehouse.value,
                start_date: dateRange.start_date,
                end_date: dateRange.end_date,
            });

            if (response.data.success) {
                setStockReportData(response.data.data);
                toast.success("Fiche de stock générée avec succès");
            } else {
                toast.error("Erreur lors de la génération de la fiche de stock");
            }
        } catch (error) {
            console.error('Error generating stock report:', error);
            toast.error("Erreur lors de la génération de la fiche de stock: " + (error.response?.data?.message || error.message));
        } finally {
            setIsGenerating(false);
        }
    };

    const handleExportPDF = async () => {
        if (!selectedProduct || !selectedWarehouse) {
            toast.error("Veuillez sélectionner un produit et un entrepôt");
            return;
        }

        try {
            const response = await axiosApi.post('stock-report/export-pdf', {
                product_id: selectedProduct.value,
                warehouse_id: selectedWarehouse.value,
                start_date: dateRange.start_date,
                end_date: dateRange.end_date,
            }, {
                responseType: 'blob'
            });

            const blob = new Blob([response.data], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `fiche-stock-${selectedProduct.label}-${dateRange.start_date}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            toast.success("PDF téléchargé avec succès");
        } catch (error) {
            console.error('Error exporting PDF:', error);
            toast.error("Erreur lors du téléchargement du PDF: " + (error.response?.data?.message || error.message));
        }
    };

    const handleExportExcel = async () => {
        if (!selectedProduct || !selectedWarehouse) {
            toast.error("Veuillez sélectionner un produit et un entrepôt");
            return;
        }

        try {
            const response = await axiosApi.post('stock-report/export-excel', {
                product_id: selectedProduct.value,
                warehouse_id: selectedWarehouse.value,
                start_date: dateRange.start_date,
                end_date: dateRange.end_date,
            }, {
                responseType: 'blob'
            });

            const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `fiche-stock-${selectedProduct.label}-${dateRange.start_date}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            toast.success("Excel téléchargé avec succès");
        } catch (error) {
            console.error('Error exporting Excel:', error);
            toast.error("Erreur lors du téléchargement de l'Excel: " + (error.response?.data?.message || error.message));
        }
    };

    return (
        <MasterLayout>
            <TopProgressBar />
            <TabTitle title="Fiche de Stock" />
            
            <Card>
                <CardHeader>
                    <h4 className="card-title mb-0">Sélection des paramètres</h4>
                </CardHeader>
                <CardBody>
                    <Row>
                        <Col md={6}>
                            <div className="mb-3">
                                <label className="form-label">Produit <span style={{color: 'red'}}>*</span></label>
                                <ReactSelect
                                    data={productOptions}
                                    onChange={setSelectedProduct}
                                    value={selectedProduct}
                                    title=""
                                    errors=""
                                    isRequired
                                    placeholder={
                                        !selectedWarehouse 
                                            ? "Sélectionnez d'abord un entrepôt"
                                            : productOptions.length === 0
                                            ? "Aucun produit trouvé dans cet entrepôt"
                                            : "Sélectionner un produit"
                                    }
                                    isDisabled={!selectedWarehouse || productOptions.length === 0}
                                />
                                {selectedWarehouse && productOptions.length === 0 && (
                                    <small className="text-muted">
                                        Aucun produit n'a de mouvements de stock dans cet entrepôt
                                    </small>
                                )}
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className="mb-3">
                                <label className="form-label">Entrepôt <span style={{color: 'red'}}>*</span></label>
                                <ReactSelect
                                    data={warehouseOptions}
                                    onChange={setSelectedWarehouse}
                                    value={selectedWarehouse}
                                    title=""
                                    errors=""
                                    isRequired
                                    placeholder="Sélectionner un entrepôt"
                                />
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="mb-3">
                                <label className="form-label">Période <span style={{color: 'red'}}>*</span></label>
                                <DateRangePicker
                                    onDateSelector={onDateSelector}
                                    isProfitReport={false}
                                />
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="d-flex gap-2">
                                <Button
                                    color="primary"
                                    onClick={handleGenerateReport}
                                    disabled={isGenerating || !selectedProduct || !selectedWarehouse}
                                >
                                    {isGenerating ? "Génération..." : "Générer la fiche"}
                                </Button>
                                {stockReportData && (
                                    <>
                                        <Button
                                            color="success"
                                            onClick={handleExportPDF}
                                        >
                                            Télécharger PDF
                                        </Button>
                                        <Button
                                            color="info"
                                            onClick={handleExportExcel}
                                        >
                                            Télécharger Excel
                                        </Button>
                                    </>
                                )}
                            </div>
                        </Col>
                    </Row>
                </CardBody>
            </Card>

            {stockReportData && (
                <StockReportTable
                    data={stockReportData}
                    currencySymbol={currencySymbol}
                    allConfigData={allConfigData}
                />
            )}
        </MasterLayout>
    );
};

const mapStateToProps = (state) => {
    const {
        isLoading,
        warehouses,
        frontSetting,
        products,
        allConfigData,
    } = state;
    return {
        isLoading,
        warehouses,
        frontSetting,
        products,
        allConfigData,
    };
};

export default connect(mapStateToProps, {
    fetchAllWarehouses,
    fetchFrontSetting,
    fetchAllProducts,
})(StockReportCard);
