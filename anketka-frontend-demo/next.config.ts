import type { NextConfig } from "next"

const nextConfig: NextConfig = {
  devIndicators: false,
  output: "standalone",
  images: {
    formats: ["image/avif", "image/webp"],
    deviceSizes: [640, 750, 828, 1080, 1200, 1920],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384, 512],
    minimumCacheTTL: 604800,
  },

  compress: true,
  poweredByHeader: false,

  experimental: {
    optimizeCss: true,
    optimizePackageImports: ["lucide-react"],
  },

  sassOptions: {
    silenceDeprecations: ["mixed-decls", "import", "legacy-js-api"],
  },

  async rewrites() {
    return [
      {
        source: `/api/:path*`,
        destination: `${process.env.NEXT_PUBLIC_API_URL}/api/:path*`,
      },
    ]
  },

  async redirects() {
    return [
      {
        source: "/sign-up",
        destination: "/register",
        permanent: true,
      },
    ]
  },
}

export default nextConfig
