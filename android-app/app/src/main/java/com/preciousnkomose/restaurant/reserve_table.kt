
package com.preciousnkomose.restaurant

import android.app.DatePickerDialog
import android.app.TimePickerDialog
import android.os.Bundle
import android.view.View
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.button.MaterialButton
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale

class reserve_table : AppCompatActivity() {

    private var selectedTableId = 1
    private var selectedTableName = "Table 1"
    private var selectedTableCapacity = 4
    private var entireVenueSelected = false

    private var selectedDate = ""
    private var selectedTime = ""
    private var guestCount = 2

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_reserve_table)

        readSelectedTable()
        displaySelectedTable()
        setupDatePicker()
        setupTimePicker()
        setupGuestStepper()
        setupBackButton()
        setupConfirmButton()
    }

    private fun readSelectedTable() {
        selectedTableId = intent.getIntExtra("selected_table_id", 1)
        selectedTableName = intent.getStringExtra("selected_table_name") ?: "Table 1"
        selectedTableCapacity = intent.getIntExtra("selected_table_capacity", 4)
        entireVenueSelected = intent.getBooleanExtra("entire_venue_selected", false)

        guestCount = if (selectedTableCapacity >= 2) 2 else 1
    }

    private fun displaySelectedTable() {
        findViewById<TextView>(R.id.text_selected_table_name).text =
            selectedTableName

        findViewById<TextView>(R.id.text_selected_table_details).text =
            if (entireVenueSelected) {
                "Entire venue • Capacity: $selectedTableCapacity guests"
            } else {
                "Capacity: $selectedTableCapacity guests"
            }

        updateGuestCount()
    }

    private fun setupDatePicker() {
        val dateSelector = findViewById<View>(R.id.layout_date_selector)

        dateSelector.setOnClickListener {
            val calendar = Calendar.getInstance()

            val datePicker = DatePickerDialog(
                this,
                { _, year, month, dayOfMonth ->
                    val chosenDate = Calendar.getInstance().apply {
                        set(year, month, dayOfMonth)
                    }

                    selectedDate = SimpleDateFormat(
                        "yyyy-MM-dd",
                        Locale.getDefault()
                    ).format(chosenDate.time)

                    findViewById<TextView>(R.id.text_selected_date).text =
                        SimpleDateFormat(
                            "EEE, dd MMM yyyy",
                            Locale.getDefault()
                        ).format(chosenDate.time)
                },
                calendar.get(Calendar.YEAR),
                calendar.get(Calendar.MONTH),
                calendar.get(Calendar.DAY_OF_MONTH)
            )

            datePicker.datePicker.minDate = calendar.timeInMillis
            datePicker.show()
        }
    }

    private fun setupTimePicker() {
        findViewById<View>(R.id.layout_time_selector).setOnClickListener {
            val calendar = Calendar.getInstance()

            TimePickerDialog(
                this,
                { _, hourOfDay, minute ->
                    selectedTime = String.format(
                        Locale.getDefault(),
                        "%02d:%02d",
                        hourOfDay,
                        minute
                    )

                    val displayTime = Calendar.getInstance().apply {
                        set(Calendar.HOUR_OF_DAY, hourOfDay)
                        set(Calendar.MINUTE, minute)
                    }

                    findViewById<TextView>(R.id.text_selected_time).text =
                        SimpleDateFormat(
                            "hh:mm a",
                            Locale.getDefault()
                        ).format(displayTime.time)
                },
                calendar.get(Calendar.HOUR_OF_DAY),
                calendar.get(Calendar.MINUTE),
                false
            ).show()
        }
    }

    private fun setupGuestStepper() {
        findViewById<View>(R.id.btn_guests_minus).setOnClickListener {
            if (guestCount > 1) {
                guestCount--
                updateGuestCount()
            } else {
                Toast.makeText(
                    this,
                    "At least one guest is required.",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }

        findViewById<View>(R.id.btn_guests_plus).setOnClickListener {
            if (guestCount < selectedTableCapacity) {
                guestCount++
                updateGuestCount()
            } else {
                Toast.makeText(
                    this,
                    "This selection has a maximum capacity of $selectedTableCapacity guests.",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }

    private fun updateGuestCount() {
        findViewById<TextView>(R.id.text_guests_count).text =
            guestCount.toString()
    }

    private fun setupBackButton() {
        findViewById<View?>(R.id.btn_back)?.setOnClickListener {
            finish()
        }
    }

    private fun setupConfirmButton() {
        findViewById<MaterialButton>(R.id.btn_confirm_booking)
            .setOnClickListener {
                validateReservation()
            }
    }

    private fun validateReservation() {
        if (selectedDate.isBlank()) {
            Toast.makeText(
                this,
                "Please select a reservation date.",
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        if (selectedTime.isBlank()) {
            Toast.makeText(
                this,
                "Please select a reservation time.",
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        if (guestCount < 1 || guestCount > selectedTableCapacity) {
            Toast.makeText(
                this,
                "Please check the number of guests.",
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        val specialNotes =
            findViewById<android.widget.EditText>(R.id.edit_special_notes)
                .text.toString().trim()

        // The API, authentication and booking database are not connected yet.
        // This confirms validation only; it does not create a real reservation.
        val message = buildString {
            append("Details validated for $selectedTableName. ")
            append("$guestCount guest(s), $selectedDate at $selectedTime.")
            if (specialNotes.isNotBlank()) {
                append(" Your special notes have been entered.")
            }
            append(" Booking service is not connected yet.")
        }

        Toast.makeText(this, message, Toast.LENGTH_LONG).show()
    }
}
