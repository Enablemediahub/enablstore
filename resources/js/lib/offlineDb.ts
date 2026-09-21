import Dexie, { type Table } from 'dexie';

export type OfflineSale = {
    id?: number;
    transactionUuid: string;
    tenantId: string;
    items: Array<{
        productId: number;
        quantity: number;
        unitPriceMinor: number;
    }>;
    totalMinor: number;
    discountType?: 'fixed' | 'percentage' | null;
    discountValue?: number;
    discountReason?: string;
    paymentMethod: 'cash' | 'mobile_money' | 'card';
    customerName?: string;
    customerPhone?: string;
    createdAt: string;
    synced: boolean;
};

class EnablstoreOfflineDb extends Dexie {
    sales!: Table<OfflineSale, number>;

    constructor() {
        super('enablstore');
        this.version(1).stores({
            sales: '++id, transactionUuid, tenantId, synced, createdAt',
        });
    }
}

export const offlineDb = new EnablstoreOfflineDb();

export const queueOfflineSale = async (sale: OfflineSale): Promise<number> =>
    offlineDb.sales.add(sale);

export const pendingOfflineSaleCount = async (): Promise<number> =>
    offlineDb.sales.filter((sale) => !sale.synced).count();

export const pendingOfflineSales = async (): Promise<OfflineSale[]> =>
    offlineDb.sales.filter((sale) => !sale.synced).toArray();

export const markOfflineSaleSynced = async (id: number): Promise<void> => {
    await offlineDb.sales.update(id, { synced: true });
};
