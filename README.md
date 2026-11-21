# Kodafirca POS - online test deploy

1. Push this repo to GitHub (hudm46-tech).
2. Create a free MySQL DB (Aiven free MySQL, TiDB Cloud, etc.) and import `database/schema.sql`.
3. Deploy on Render.com: New > Web Service > connect repo > Runtime: Docker (free plan).
4. Set environment variables: APP_ENV=production, APP_DEBUG=false, APP_URL=https://<your-app>.onrender.com,
   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, TIMEZONE=Africa/Dar_es_Salaam
Note: free hosts have an ephemeral disk, so uploads (logos, receipts) are lost on restart.
