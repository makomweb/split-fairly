Development:

- Start Symfony backend:

  cd backend
  symfony server:start --no-tls

- Start frontend (HMR):

  cd frontend
  npm install
  npm run dev

Production build (outputs to backend/public/build):

  cd frontend
  npm install
  npm run build

Open the Symfony host (http://127.0.0.1:8000/) which will redirect to Vite in development or serve the built files in production.
