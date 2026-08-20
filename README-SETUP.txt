VERCEL SMM COMBO SETUP

1) Upload this whole folder to GitHub and import it into Vercel.
2) Create an Upstash Redis database (REST API).
3) In Vercel Project Settings > Environment Variables, add:
   UPSTASH_REDIS_REST_URL = your Upstash REST URL
   UPSTASH_REDIS_REST_TOKEN = your Upstash REST token
   ADMIN_PASSWORD = a strong admin password
   MRSMM_API_KEY = your new MrSMM API key
   MRSMM_API_URL = https://mrsmm.org/api/v2

4) Redeploy.
5) Open:
   https://YOUR-DOMAIN.vercel.app/admin

The API key is NOT placed in index.html. Admin changes to services/settings are stored in Upstash Redis.

IMPORTANT:
- Do not commit API keys or passwords to GitHub.
- Regenerate the API key that was previously exposed in chat.
- The default sample services are placeholders; replace their IDs with your real MrSMM service IDs in /admin.
