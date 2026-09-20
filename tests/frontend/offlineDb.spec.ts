import 'fake-indexeddb/auto';

import { describe, expect, it } from 'vitest';

import {
    offlineDb,
    pendingOfflineSaleCount,
    queueOfflineSale,
} from '@/lib/offlineDb';

describe('offline sale queue', () => {
    it('stores pending sales in IndexedDB', async () => {
        await offlineDb.sales.clear();

        await queueOfflineSale({
            transactionUuid: 'sale-1',
            tenantId: 'demo',
            items: [],
            totalMinor: 1000,
            paymentMethod: 'cash',
            createdAt: new Date().toISOString(),
            synced: false,
        });

        expect(await pendingOfflineSaleCount()).toBe(1);
    });
});
