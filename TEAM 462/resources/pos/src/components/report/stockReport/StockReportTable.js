import React from "react";
import { Card, CardBody, CardHeader, Table, Row, Col, Badge } from "reactstrap";
import { currencySymbolHandling, getFormattedMessage } from "../../../shared/sharedMethod";

const StockReportTable = ({ data, currencySymbol, allConfigData }) => {
    if (!data) return null;

    const { product, warehouse, period, summary, movements } = data;

    return (
        <div className="mt-4">
            {/* Informations du produit et de la période */}
            <Card className="mb-4">
                <CardHeader>
                    <h4 className="card-title mb-0">Informations du rapport</h4>
                </CardHeader>
                <CardBody>
                    <Row>
                        <Col md={6}>
                            <div className="mb-2">
                                <strong>Produit:</strong> {product.name} ({product.code})
                            </div>
                            <div className="mb-2">
                                <strong>Catégorie:</strong> {product.category}
                            </div>
                            <div className="mb-2">
                                <strong>Marque:</strong> {product.brand}
                            </div>
                        </Col>
                        <Col md={6}>
                            <div className="mb-2">
                                <strong>Entrepôt:</strong> {warehouse.name}
                            </div>
                            <div className="mb-2">
                                <strong>Période:</strong> {period.start_date} - {period.end_date}
                            </div>
                        </Col>
                    </Row>
                </CardBody>
            </Card>


            {/* Tableau des mouvements */}
            <Card>
                <CardHeader>
                    <h4 className="card-title mb-0">Détail des mouvements</h4>
                </CardHeader>
                <CardBody>
                    <div className="table-responsive">
                        <Table striped hover>
                            <thead>
                                <tr>
                                    <th style={{backgroundColor: '#007bff', color: 'white'}}>Date</th>
                                    <th style={{backgroundColor: '#007bff', color: 'white'}}>Nom du produit</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Stock initial</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Entrée</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Total</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Sortie</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Stock final</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Prix unitaire</th>
                                    <th className="text-end" style={{backgroundColor: '#007bff', color: 'white'}}>Prix total</th>
                                    <th style={{backgroundColor: '#007bff', color: 'white'}}>Type de mouvement</th>
                                    <th style={{backgroundColor: '#007bff', color: 'white'}}>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                {movements.map((movement, index) => (
                                    <tr key={index}>
                                        <td style={{backgroundColor: '#e3f2fd'}}>{new Date(movement.date).toLocaleDateString('fr-FR')}</td>
                                        <td style={{backgroundColor: '#e3f2fd'}}>{movement.product_name}</td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>{movement.stock_initial}</td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>
                                            {movement.entree > 0 && (
                                                <Badge color="success">+{movement.entree}</Badge>
                                            )}
                                        </td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>{movement.total}</td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>
                                            {movement.sortie > 0 && (
                                                <Badge color="danger">-{movement.sortie}</Badge>
                                            )}
                                        </td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>
                                            <Badge color="info">{movement.stock_final}</Badge>
                                        </td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>
                                            {currencySymbolHandling(
                                                allConfigData,
                                                currencySymbol,
                                                movement.prix_unitaire
                                            )}
                                        </td>
                                        <td className="text-end" style={{backgroundColor: '#e3f2fd'}}>
                                            {currencySymbolHandling(
                                                allConfigData,
                                                currencySymbol,
                                                movement.prix_total
                                            )}
                                        </td>
                                        <td style={{backgroundColor: '#e3f2fd'}}>
                                            <Badge 
                                                color={
                                                    movement.movement_type === 'purchase' ? 'success' :
                                                    movement.movement_type === 'sale' ? 'danger' :
                                                    movement.movement_type === 'return' ? 'warning' :
                                                    movement.movement_type === 'adjustment' ? 'info' :
                                                    'secondary'
                                                }
                                            >
                                                {movement.movement_type === 'purchase' ? 'Achat' :
                                                 movement.movement_type === 'sale' ? 'Vente' :
                                                 movement.movement_type === 'return' ? 'Retour' :
                                                 movement.movement_type === 'adjustment' ? 'Ajustement' :
                                                 movement.movement_type === 'transfer_in' ? 'Transfert entrant' :
                                                 movement.movement_type === 'transfer_out' ? 'Transfert sortant' :
                                                 movement.movement_type === 'initial' ? 'Stock initial' :
                                                 movement.movement_type}
                                            </Badge>
                                        </td>
                                        <td style={{backgroundColor: '#e3f2fd'}}>{movement.notes || '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </Table>
                    </div>
                </CardBody>
            </Card>
        </div>
    );
};

export default StockReportTable;
