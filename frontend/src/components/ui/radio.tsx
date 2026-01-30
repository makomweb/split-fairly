import * as React from "react"
import { cn } from "@/lib/utils"

export interface RadioGroupProps extends React.HTMLAttributes<HTMLDivElement> {
  value?: string
  onValueChange?: (v: string) => void
}

export const RadioGroup = React.forwardRef<
  React.ElementRef<HTMLDivElement>,
  RadioGroupProps
>(({ className, children, value, onValueChange, ...props }, ref) => (
  <div ref={ref} role="radiogroup" className={cn("flex gap-2 items-center", className)} {...props}>
    {React.Children.map(children, (child) => {
      if (!React.isValidElement(child)) return child
      return React.cloneElement(child, { _groupValue: value, _onChange: onValueChange })
    })}
  </div>
))
RadioGroup.displayName = "RadioGroup"

export interface RadioOptionProps extends React.InputHTMLAttributes<HTMLInputElement> {
  value: string
  children?: React.ReactNode
  _groupValue?: string
  _onChange?: (v: string) => void
}

export const RadioOption = React.forwardRef<
  React.ElementRef<HTMLInputElement>,
  RadioOptionProps
>(({ className, value, children, _groupValue, _onChange, ...props }, ref) => {
  const selected = _groupValue === value

  return (
    <label className={cn("inline-flex items-center gap-2 text-sm font-medium", className)}>
      <input
        ref={ref}
        type="radio"
        role="radio"
        aria-checked={selected}
        checked={selected}
        value={value}
        onChange={() => _onChange?.(value)}
        className="form-radio"
        {...props}
      />
      <span>{children}</span>
    </label>
  )
})
RadioOption.displayName = "RadioOption"
