<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Unit;
use App\Models\AuditLog;
use App\Services\StockEngine;

class ItemController extends Controller
{
    private Item $itemModel;
    private ItemGroup $groupModel;
    private Unit $unitModel;

    public function __construct()
    {
        parent::__construct();
        $this->itemModel = new Item();
        $this->groupModel = new ItemGroup();
        $this->unitModel = new Unit();
    }

    public function index(): void
    {
        $search = $this->request->input('search');
        $groupId = $this->request->input('group_id') ? (int)$this->request->input('group_id') : null;

        $items = $this->itemModel->getItemsWithDetails($search, $groupId);
        $groups = $this->groupModel->all([], 'name ASC');

        $this->view('masters.items.index', [
            'pageTitle' => 'Items & Inventory',
            'items' => $items,
            'groups' => $groups,
            'search' => $search,
            'selectedGroup' => $groupId
        ]);
    }

    public function create(): void
    {
        $companyId = Session::getCompanyId();
        $groups = $this->groupModel->all([], 'name ASC');
        $units = $this->unitModel->getAvailableUnits($companyId);

        $this->view('masters.items.form', [
            'pageTitle' => 'Add New Item',
            'item' => null,
            'groups' => $groups,
            'units' => $units
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $fyId = Session::getFinancialYearId();

        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $barcode = trim($this->request->input('barcode', ''));
        $groupId = $this->request->input('group_id') ? (int)$this->request->input('group_id') : null;
        $unitId = $this->request->input('unit_id') ? (int)$this->request->input('unit_id') : null;
        $hsnSac = trim($this->request->input('hsn_sac', ''));
        $gstRate = (float)$this->request->input('gst_rate', 0.00);
        $purchaseRate = (float)$this->request->input('purchase_rate', 0.00);
        $sellingRate = (float)$this->request->input('selling_rate', 0.00);
        $mrp = (float)$this->request->input('mrp', 0.00);
        $openingStock = (float)$this->request->input('opening_stock', 0.000);
        $minStock = (float)$this->request->input('min_stock', 0.000);
        $maxStock = (float)$this->request->input('max_stock', 0.000);
        $reorderLevel = (float)$this->request->input('reorder_level', 0.000);
        $description = trim($this->request->input('description', ''));

        if (empty($name)) {
            $this->flash('danger', 'Item name is required.');
            $this->redirect('/items/create');
        }

        $itemId = $this->itemModel->create([
            'company_id' => $companyId,
            'name' => $name,
            'code' => $code ?: null,
            'barcode' => $barcode ?: null,
            'group_id' => $groupId,
            'unit_id' => $unitId,
            'hsn_sac' => $hsnSac ?: null,
            'gst_rate' => $gstRate,
            'purchase_rate' => $purchaseRate,
            'selling_rate' => $sellingRate,
            'mrp' => $mrp,
            'opening_stock' => $openingStock,
            'opening_stock_value' => round($openingStock * $purchaseRate, 2),
            'min_stock' => $minStock,
            'max_stock' => $maxStock,
            'reorder_level' => $reorderLevel,
            'description' => $description,
            'status' => 'active'
        ]);

        // Record opening stock movement if opening stock > 0
        if ($openingStock > 0 && $fyId) {
            StockEngine::recordMovement(
                $companyId,
                $fyId,
                $itemId,
                'opening',
                $itemId,
                null,
                date('Y-m-d'),
                $openingStock,
                0,
                $purchaseRate,
                round($openingStock * $purchaseRate, 2),
                'Initial Opening Stock'
            );
        }

        AuditLog::log('Item Created', 'Items', $itemId);
        $this->flash('success', "Item [{$name}] added successfully.");
        $this->redirect('/items');
    }

    public function edit(int $id): void
    {
        $companyId = Session::getCompanyId();
        $item = $this->itemModel->find($id);

        if (!$item) {
            $this->flash('danger', 'Item not found or access denied.');
            $this->redirect('/items');
        }

        $groups = $this->groupModel->all([], 'name ASC');
        $units = $this->unitModel->getAvailableUnits($companyId);

        $this->view('masters.items.form', [
            'pageTitle' => 'Edit Item — ' . $item['name'],
            'item' => $item,
            'groups' => $groups,
            'units' => $units
        ]);
    }

    public function update(int $id): void
    {
        $this->validateCsrf();
        $item = $this->itemModel->find($id);

        if (!$item) {
            $this->flash('danger', 'Item not found.');
            $this->redirect('/items');
        }

        $name = trim($this->request->input('name', ''));
        $code = trim($this->request->input('code', ''));
        $barcode = trim($this->request->input('barcode', ''));
        $groupId = $this->request->input('group_id') ? (int)$this->request->input('group_id') : null;
        $unitId = $this->request->input('unit_id') ? (int)$this->request->input('unit_id') : null;
        $hsnSac = trim($this->request->input('hsn_sac', ''));
        $gstRate = (float)$this->request->input('gst_rate', 0.00);
        $purchaseRate = (float)$this->request->input('purchase_rate', 0.00);
        $sellingRate = (float)$this->request->input('selling_rate', 0.00);
        $mrp = (float)$this->request->input('mrp', 0.00);
        $minStock = (float)$this->request->input('min_stock', 0.000);
        $maxStock = (float)$this->request->input('max_stock', 0.000);
        $reorderLevel = (float)$this->request->input('reorder_level', 0.000);
        $description = trim($this->request->input('description', ''));

        $this->itemModel->update($id, [
            'name' => $name,
            'code' => $code ?: null,
            'barcode' => $barcode ?: null,
            'group_id' => $groupId,
            'unit_id' => $unitId,
            'hsn_sac' => $hsnSac ?: null,
            'gst_rate' => $gstRate,
            'purchase_rate' => $purchaseRate,
            'selling_rate' => $sellingRate,
            'mrp' => $mrp,
            'min_stock' => $minStock,
            'max_stock' => $maxStock,
            'reorder_level' => $reorderLevel,
            'description' => $description
        ]);

        AuditLog::log('Item Updated', 'Items', $id);
        $this->flash('success', "Item [{$name}] updated successfully.");
        $this->redirect('/items');
    }

    public function delete(int $id): void
    {
        $item = $this->itemModel->find($id);
        if ($item) {
            $this->itemModel->delete($id);
            AuditLog::log('Item Deleted', 'Items', $id);
            $this->flash('success', "Item [{$item['name']}] deleted successfully.");
        }
        $this->redirect('/items');
    }

    public function search(): void
    {
        $query = $this->request->input('q', '');
        $items = $this->itemModel->getItemsWithDetails($query);
        $this->json(['success' => true, 'items' => $items]);
    }

    public function findByBarcode(string $code): void
    {
        $item = $this->itemModel->findByBarcode($code);
        if ($item) {
            $this->json(['success' => true, 'item' => $item]);
        } else {
            $this->json(['success' => false, 'message' => 'Item not found for barcode: ' . $code], 404);
        }
    }

    // Units and Groups Management
    public function units(): void
    {
        $this->unitsIndex();
    }

    public function unitsIndex(): void
    {
        $companyId = Session::getCompanyId();
        $units = $this->unitModel->getAvailableUnits($companyId);
        $groups = $this->groupModel->all([], 'name ASC');

        $this->view('masters.units.index', [
            'pageTitle' => 'Units & Item Groups',
            'units' => $units,
            'groups' => $groups
        ]);
    }

    public function storeUnit(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $name = trim($this->request->input('name', ''));
        $shortName = strtoupper(trim($this->request->input('short_name', '')));
        $decimals = (int)$this->request->input('decimal_places', 0);

        if (!empty($name) && !empty($shortName)) {
            $this->unitModel->create([
                'company_id' => $companyId,
                'name' => $name,
                'short_name' => $shortName,
                'decimal_places' => $decimals,
                'is_system' => 0,
                'status' => 'active'
            ]);
            $this->flash('success', "Unit [{$shortName}] added.");
        }
        $this->redirect('/units');
    }

    public function storeGroup(): void
    {
        $this->validateCsrf();
        $companyId = Session::getCompanyId();
        $name = trim($this->request->input('name', ''));
        $description = trim($this->request->input('description', ''));

        if (!empty($name)) {
            $this->groupModel->create([
                'company_id' => $companyId,
                'name' => $name,
                'description' => $description,
                'status' => 'active'
            ]);
            $this->flash('success', "Item group [{$name}] added.");
        }
        $this->redirect('/units');
    }
}
