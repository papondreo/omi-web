import { toast } from "react-toastify"
import { CustomToast } from "./custom-toast"

export const showSuccessToast = (message: string, subtitle?: string) => {
  toast.success(<CustomToast message={message} subtitle={subtitle} />)
}

export const showErrorToast = (message: string, subtitle?: string) => {
  toast.error(<CustomToast message={message} subtitle={subtitle} />)
}
